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

class YubiKey_Authenticator {

  private static $id = "19628";
  private static $key = "u0W17Of4vqfpKnjZi3BXhA9H6jQ=";

  public static function authenticate($username, $password) {

    Pimcore_Log_Simple::log("YubiKey", "Authenticating User ".$username);

    $pimcore_user = User::getByName($username);
    if (! $pimcore_user instanceof User) {
      Pimcore_Log_Simple::log("YubiKey", "User ".$username." nicht gefunden.");

      return null;
    }

    $yubikey_user = YubiKey_User::getById($pimcore_user->getId());

    if (is_null($yubikey_user)) return null;

    if (!$yubikey_user->getActivelocal()) {
      // TODO hier kommt jetzt der remote-Teil
      return null;
    }

    $yubico = new Auth_Yubico(self::$id, self::$key);

    $serial = substr($password, 0, 12);

    foreach ($yubikey_user->getKeys() as $key) {
      if ($key["serial"] == $serial) {
        try {
          $yubico->verify($password);

        } catch (Exception $e) {
          Pimcore_Log_Simple::log("YubiKey", "Authentication failed: " . $e->getMessage());
          Pimcore_Log_Simple::log("YubiKey", "Debug output from server: ".$yubico->getLastResponse());

          return null;
        }

        Pimcore_Log_Simple::log("YubiKey", "Success Authenticating User ".$username);
        $user = User::getByName($username);
        return $user;

      }
    }
    return null;

  }

}