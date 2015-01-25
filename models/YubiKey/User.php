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

namespace YubiKey;
use Pimcore\Model;

/**
 * Class User
 * @package YubiKey
 */
class User {

  private $id;
  private $active_local;
  private $keys;


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
    return $this->active_local;
  }

  /**
   * @param mixed $active_local
   */
  public function setActivelocal($active_local) {
    $this->active_local = $active_local;
  }

  /**
   * @return \Pimcore\Model\User
   */
  public function getPimcoreUser() {
    $user = Model\User\AbstractUser::getById($this->getId());
    return $user;
  }

  /**
   * @param $id
   * @return null|\YubiKey\User
   */
  public static function getById($id) {
    $xml_content = file_get_contents(YUBIKEY_PLUGIN_VAR.DIRECTORY_SEPARATOR."users.xml");
    $xml = simplexml_load_string($xml_content);
    $users = $xml->xpath("//user[@id=\"".$id."\"]");

    if (sizeof($users) > 0) {
      $user = $users[0];

      $keys = array();
      foreach ($user->keys->key as $key) {
        /** @var \SimpleXMLElement $key */
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

  /**
   * Saves the user to the XML file.
   */
  public function save() {

    $dom = new \DOMDocument();
    $dom->load(YUBIKEY_PLUGIN_VAR.DIRECTORY_SEPARATOR."users.xml");

    /** @var \DOMElement $users_node */
    $users_node = $dom->getElementsByTagName("users")->item(0);

    /** @var \DOMNodeList $userNodes */
    $userNodes = $users_node->getElementsByTagName("user");
    foreach ($userNodes as $userNode) {
      /** @var \DOMElement $userNode */
      if ($userNode->getAttribute("id") == $this->getId()) {
        $users_node->removeChild($userNode);
      }
    }

    $user = $dom->createElement("user");
    $users_node->appendChild($user);
    $user->setAttribute("id", $this->getId());

    $keys = $dom->createElement("keys");
    $user->appendChild($keys);
    foreach ($this->getKeys() as $key) {
      $xml_key = $dom->createElement("key");
      $keys->appendChild($xml_key);

      $serial = $dom->createElement("serial", $key["serial"]);
      $xml_key->appendChild($serial);

      $comment = $dom->createElement("comment", $key["comment"]);
      $xml_key->appendChild($comment);
    }

    $active_local = $dom->createElement("activelocal", $this->getActivelocal() ? 1 : 0);
    $user->appendChild($active_local);

    $dom->save(YUBIKEY_PLUGIN_VAR.DIRECTORY_SEPARATOR."users.xml");

    return;
  }
}

