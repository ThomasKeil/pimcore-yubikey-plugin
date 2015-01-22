<?php
/**
 * Created by PhpStorm.
 * User: thomas
 * Date: 12.01.15
 * Time: 19:58
 */

namespace YubiKey\Crypt;

class Rsa extends \Zend_Crypt_Rsa {
  /**
   * Verify signature with public key
   *
   * @param  string $data
   * @param  string $signature
   * @param  null|\Zend_Crypt_Rsa_Key_Public $publicKey
   * @return bool
   * @throws \Exception
   */
  public function verify($data, $signature, \Zend_Crypt_Rsa_Key_Public $publicKey = null) {
    if (null === $publicKey) {
      $publicKey = $this->getPublicKey();
    }

    // check if signature is encoded in Base64
    $output = base64_decode($signature, true);
    if (false !== $output) {
      $signature = $output;
    }

    $result = openssl_verify(
      $data,
      $signature,
      $publicKey->getOpensslKeyResource(),
      $this->getHashAlgorithm()
    );
    if (-1 === $result) {
      throw new \Exception(
        'Can not verify signature; openssl ' . openssl_error_string()
      );
    }

    return ($result === 1);
  }
}