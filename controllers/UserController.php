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

class YubiKey_UserController extends Pimcore_Controller_Action_Admin {

  public function loadAction() {
    $id = $this->getParam("id");

    $user = YubiKey_User::getById($id);

    if (is_null($user)) {
      $this->_helper->json(array("success" => false, "message" => "User not found"));
    }

    // Die Keys müssen umgewandelt werden weil die ExtJS-Stores halt hirnrissig sind
    $keys = array();
    foreach ($user->getKeys() as $key) {
      $keys[] = array($key["serial"], $key["comment"]);
    }

    $data = array(
      "success" => "true",
      "yubikey" => array(
        "activelocal" => $user->getActivelocal() ? 1 : 0,
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

    $yubikey_user = YubiKey_User::getById($id);
    if (is_null($yubikey_user)) {
      $yubikey_user = new YubiKey_User();
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
