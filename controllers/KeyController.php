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

/**
 * Class YubiKey_KeyController
 *
 * Handles the RSA-Keys for the local configuration
 */
class YubiKey_KeyController extends \Pimcore\Controller\Action\Admin {

  public function createAction() {
    if (!function_exists("openssl_encrypt")) {
      $this->_helper->json(array(
        "success" => true,
        "message" => "OpenSSL extension not installed."
      ));
    }

    $crypt = new Zend_Crypt_Rsa();

    /** @var ArrayObject $keys */
    $keys = $crypt->generateKeys();

    $this->_helper->json(array(
      "success" => true,
      "keys" => array(
        "public" => $keys["publicKey"]->toString(),
        "private" => $keys["privateKey"]->toString()
      )
    ));

  }

}