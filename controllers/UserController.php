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

class YubiKey_UserController extends \Pimcore\Controller\Action\Admin {

  /**
   * Reads the data of a user
   */
  public function loadAction() {
    $id = $this->getParam("id");

    $this->protectCSRF();

    $yubikey_user = \YubiKey\User::getById($id);

    if (is_null($yubikey_user)) {
      $this->_helper->json(array("success" => false, "message" => "User not found"));
    }

    $pimcore_user = \Pimcore\Model\User::getById(intval($this->getParam("id")));

    if($pimcore_user instanceof \Pimcore\Model\User && $pimcore_user->isAdmin() && !$yubikey_user->getPimcoreUser()->isAdmin()) {
      $this->_helper->json(array("success" => false, "message" => "Only admin users are allowed to modify admin users"));
    }

    // Umwandlung der Keys
    $keys = array();
    foreach ($yubikey_user->getKeys() as $key) {
      $keys[] = array($key["serial"], $key["comment"]);
    }

    $data = array(
      "success" => "true",
      "yubikey" => array(
        "activelocal" => $yubikey_user->getActivelocal() ? 1 : 0,
        "keys" => $keys
      )
    );
    $this->_helper->json($data);
  }

  /**
   * Saves the data of a user.
   */
  public function saveAction() {
    $id = $this->getParam("id");
    $keymapping = (array)json_decode($this->getParam("keymapping"));

    $user_info = array(
      "activelocal" => $keymapping["activelocal"]
    );

    $keys = array();
    if (is_array($keymapping["keys"])) {
      foreach ($keymapping["keys"] as $key) {
        $key = (array)$key;
        $keys[] = array("serial" => $key["serial"], "comment" => $key["comment"]);
      }
      $user_info["keys"] = $keys;
    }

    $yubikey_user = \YubiKey\User::getById($id);
    if (is_null($yubikey_user)) {
      $yubikey_user = new \YubiKey\User();
      $yubikey_user->setId($id);
    }
    $yubikey_user->setActivelocal($keymapping["activelocal"] == 1);
    $yubikey_user->setKeys($keys);
    $yubikey_user->save();

    $data = array(
      "success" => "true"
    );
    $this->_helper->json($data);
  }
}
