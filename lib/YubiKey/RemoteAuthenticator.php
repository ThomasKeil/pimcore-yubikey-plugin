<?php
/**
 * Created by PhpStorm.
 * User: thomas
 * Date: 27.12.14
 * Time: 14:06
 */

namespace YubiKey;
use Pimcore;

class RemoteAuthenticator {

    /**
     * @param $username
     * @param $password
     * @return null|\Pimcore\Model\User
     */
    public static function authenticate($username, $password) {
        $user = null;

        $config = Config::getInstance();
        $data = $config->getData();
        $remotePublicKey = new \Zend_Crypt_Rsa_Key_Public($data["yubikey"]["remote"]["publickey"]);
        $localPrivateKey = new \Zend_Crypt_Rsa_Key_Private($data["yubikey"]["local"]["privatekey"]);

        if (empty($remotePublicKey)) {
            Logger::error("Remote public key not set");
            return null;
        }

        if (empty($localPrivateKey)) {
            Logger::error("Local private key not set");
            return null;
        }

        $request = array(
          "username" => $username,
          "password" => $password,
          "identifier" => $data["yubikey"]["remote"]["identifier"]
        );

        $request_json = json_encode($request);

        $crypt = new Crypt\Rsa();

        $encrypted = $crypt->encrypt($request_json, $remotePublicKey);

        $signature = $crypt->sign($request_json, $localPrivateKey, $crypt::BASE64);
        Logger::debug("Signature: ".$signature);

        $server = $data["yubikey"]["remote"]["server"];

        $client = new \Zend_Http_Client();
        $client->setUri($server."/plugin/YubiKeyRemoteAuthenticator/auth/auth");
        $client->resetParameters();
        $client->setParameterPost("method", "zend_crypt_rsa");
        $client->setParameterPost("message", $encrypted);
        $client->setParameterPost("signature", $signature);


        /** @var \Zend_Http_Response $response */
        $response = $client->request("POST");

        if ($response->isError()) {
            Logger::log("Error remote authenticating: ".$response->getStatus().": ".$response->getMessage());
            return null;
        }

        $body = $response->getBody();
        Logger::debug("Received Body: ".$body);

        try {
            $json = \Zend_Json_Decoder::decode($body);
        } catch (\Zend_Json_Exception $e) {
            Logger::log("Error remote authenticating, response is not json: ".$response->getStatus().": ".$response->getMessage());
            return null;
        }

        if (!is_array($json)) {
            Logger::log("Error remote authenticating, response is not array: ".$response->getStatus().": ".$response->getBody());
            return null;
        }

        switch ($json["code"]) {
            case 200:
                $signature = base64_decode($json["signature"]);
                $encrypted_message = base64_decode($json["message"]);
                $decrypted_message = $crypt->decrypt($encrypted_message, $localPrivateKey);
                Logger::debug("Received decrypted Body: ".$decrypted_message);

                $message = \Zend_Json_Decoder::decode($decrypted_message);

                $authentic = $crypt->verify($decrypted_message, $signature, $remotePublicKey);
                if (!$authentic) {
                    Logger::log("Message is not authentic.");
                    return null;
                }

                $authenticated_username = $message["username"];
                $pimcore_user = Pimcore\Model\User::getByName($authenticated_username);
                if (! $pimcore_user instanceof Pimcore\Model\User) {
                    Logger::log("User ".$authenticated_username." as specified by RemoteAuth not found.");
                    return null;
                }
                return $pimcore_user;
                break;

            case 404:
                Logger::log("User not found by RemoteAuth");
                return null;
                break;

            default:
                Logger::log("Error remote authenticating. Message: ".$json["message"]);
                return null;
        }
    }
}