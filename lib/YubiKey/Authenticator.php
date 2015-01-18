<?php
 /**
 * Created by Thomas Keil - Weblizards GmbH.
 * User: Thomas Keil
 * Email: thomas@weblizards.de
 *
 * Date: 25.11.14
 * Time: 12:31
 *
 * Dieser Quellcode ist geistiges Eigentum der Weblizards GmbH
 * und darf ohne vorheriges schriftliches Einverst�ndnis nicht
 * vervielf�ltigt werden.
 *
 */

namespace YubiKey;

use Pimcore;
use Pimcore\Model;
use Auth;

class Authenticator {

  private static $id = "19628";
  private static $key = "u0W17Of4vqfpKnjZi3BXhA9H6jQ=";

  /**
   * @param $username
   * @param $password
   * @return null|\Pimcore\Model\User
   */
  public static function authenticate($username, $password) {

    Logger::log("Authenticating User ".$username);

    $pimcore_user = Pimcore\Model\User::getByName($username);
    if (! $pimcore_user instanceof Pimcore\Model\User) {
      Logger::log("User ".$username." nicht gefunden.");
      return null;
    }

    $yubikey_user = User::getById($pimcore_user->getId());

    if (is_null($yubikey_user)) return null;

    if (!$yubikey_user->getActivelocal()) {
      return null;
    }

    $yubico = new Auth\Yubico(self::$id, self::$key);

    $serial = substr($password, 0, 12);

    foreach ($yubikey_user->getKeys() as $key) {
      if ($key["serial"] == $serial) {
        try {
          $yubico->verify($password);
        } catch (\Exception $e) {
          Logger::log("Authentication failed: " . $e->getMessage());
          Logger::debug("Debug output from server: ".$yubico->getLastResponse());
          return null;
        }

        Logger::log("Success Authenticating User ".$username);
        return $pimcore_user;
      }
    }
    return null;
  }
}