<?php
 /**
 * Created by Thomas Keil - Weblizards GmbH.
 * User: Thomas Keil
 * Email: thomas@weblizards.de
 *
 * Date: 26.11.14
 * Time: 18:12
 *
 * Dieser Quellcode ist geistiges Eigentum der Weblizards GmbH
 * und darf ohne vorheriges schriftliches Einverst�ndnis nicht
 * vervielf�ltigt werden.
 *
 */

namespace YubiKey;

class UserController extends \Pimcore\Controller\Action\Admin {

  public function loadAction() {
    $id = $this->getParam("id");

    $this->protectCSRF();

    $yubikey_user = User::getById($id);

    if (is_null($yubikey_user)) {
      $this->_helper->json(array("success" => false, "message" => "User not found"));
    }

    $pimcore_user = \Pimcore\Model\User::getById(intval($this->getParam("id")));

    if($pimcore_user instanceof \Pimcore\Model\User && $pimcore_user->isAdmin() && !$yubikey_user->getPimcoreUser()->isAdmin()) {
      throw new \Exception("Only admin users are allowed to modify admin users");
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

    $yubikey_user = User::getById($id);
    if (is_null($yubikey_user)) {
      $yubikey_user = new User();
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
