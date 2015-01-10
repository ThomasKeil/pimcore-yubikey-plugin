<?php
/**
 * Created by PhpStorm.
 * User: thomas
 * Date: 10.01.15
 * Time: 17:42
 */

class YubiKey_KeyController extends \Pimcore\Controller\Action\Admin {

  public function createAction() {
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