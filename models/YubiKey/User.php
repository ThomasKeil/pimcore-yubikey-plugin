<?php
 /**
 * Created by Thomas Keil - Weblizards GmbH.
 * User: Thomas Keil
 * Email: thomas@weblizards.de
 *
 * Date: 28.11.14
 * Time: 16:13
 *
 * Dieser Quellcode ist geistiges Eigentum der Weblizards GmbH
 * und darf ohne vorheriges schriftliches Einverständnis nicht
 * vervielfältigt werden.
 *
 */

class YubiKey_User {

  private $id;
  private $activelocal;
  private $keys;
  private $pimcore_user;

  /**
   * @return mixed
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @param mixed $id
   */
  public function setId($id) {
    $this->id = $id;
  }



  /**
   * @return mixed
   */
  public function getKeys() {
    return $this->keys;
  }

  /**
   * @param mixed
   */
  public function setKeys($keys) {
    $this->keys = $keys;
  }

  /**
   * @return mixed
   */
  public function getActivelocal() {
    return $this->activelocal;
  }

  /**
   * @param mixed $activelocal
   */
  public function setActivelocal($activelocal) {
    $this->activelocal = $activelocal;
  }

  /**
   * @return User
   */
  public function getPimcoreUser() {
    $user = User::getById($this->getId());
    return $user;
  }

  /**
   * @param $id
   * @return null|YubiKey_User
   */
  public static function getById($id) {
    $xml_content = file_get_contents(YUBIKEY_PLUGIN_VAR.DIRECTORY_SEPARATOR."users.xml");
    $xml = simplexml_load_string($xml_content);
    $users = $xml->xpath("//user[@id=\"".$id."\"]");

    if (sizeof($users) > 0) {
      $user = $users[0];

      $keys = array();
      foreach ($user->keys->key as $key) {
        /** @var SimpleXMLElement $key */
        $keys[] = array(
          "serial" => substr($key->serial->__toString(), 0, 12),
          "comment" => $key->comment->__toString()
        );
      }
      $yubikey_user = new self();
      $yubikey_user->setId($id);
      $yubikey_user->setKeys($keys);
      $yubikey_user->setActivelocal($user->activelocal->__toString() == 1);
      return $yubikey_user;
    }

    return null;
  }


  /**
   * @return array
   */
  public function toArray() {

    $serials = array();
    foreach ($this->keys as $serial) {
      $serials[] = $serial;
    }

    return array(
      "id" => $this->getId(),
      "activelocal" => $this->getActivelocal() ? 1 : 0,
      "keys" => $this->getKeys()
    );
  }

  public function save() {
    // TODO: Umstellen auf DOM, da hier das Delete nicht funktioniert.
    $xml = simplexml_load_file(YUBIKEY_PLUGIN_VAR.DIRECTORY_SEPARATOR."users.xml");
    $users_list = $xml->xpath("//user[@id=".$this->getId()."]");

    foreach ($users_list as $user) { // Sollte eh nur einer sein
      $user->parentNode->removeChild($user);
    }

    $user = $xml->addChild("user");
    $user->addAttribute("id", $this->getId());

    $activelocal = $user->addChild("activelocal", $this->getActivelocal() ? 1 : 0);

    $keys = $user->addChild("keys");
    foreach ($this->getKeys() as $key) {
      $xml_key = $keys->addChild("key");
      $xml_key->addChild("serial", $key["serial"]);
      $xml_key->addChild("comment", $key["comment"]);
    }

    $result = $xml->asXML(YUBIKEY_PLUGIN_VAR . DIRECTORY_SEPARATOR . "users.xml");
    if ($result === false) {
      throw new Exception("Error writing ".YUBIKEY_PLUGIN_VAR . DIRECTORY_SEPARATOR . "users.xml");
    }
  }
}

