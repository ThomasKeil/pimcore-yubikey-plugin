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

        $request = array(
          "username" => $username,
          "password" => $password,
          "identifier" => $data["yubikey"]["remote"]["identifier"]
        );

        $crypt = new \Zend_Crypt_Rsa();

        $encrypted = $crypt->encrypt(json_encode($request), $remotePublicKey);
        $signature = $crypt->sign($request, $localPrivateKey);

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

        $decrypted_body = $crypt->decrypt($body, $localPrivateKey);
        Logger::debug("Received decrypted Body: ".$decrypted_body);

        $json = \Zend_Json_Decoder::decode($decrypted_body);

        switch ($json["code"]) {
            case 200:
                $authenticated_username = $json["username"];
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
                Logger::log("Error remote authenticating. Body: ".$decrypted_body);
                return null;
        }
    }
}