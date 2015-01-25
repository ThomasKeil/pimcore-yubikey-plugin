<?php
/**
 * This source file is subject to the new BSD license that is
 * available through the world-wide-web at this URL:
 * http://www.pimcore.org/license
 *
 * @category   Pimcore
 * @copyright  Copyright (c) 2015 Weblizards GmbH (http://www.weblizards.de)
 * @author     Thomas Keil <thomas@weblizards.de>
 * @license    http://www.pimcore.org/license     New BSD License
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