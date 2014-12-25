<?php

if (!defined("YUBIKEY_PLUGIN_PATH")) define("YUBIKEY_PLUGIN_PATH", PIMCORE_PLUGINS_PATH."/YubiKey");
if (!defined("YUBIKEY_PLUGIN_VAR"))  define("YUBIKEY_PLUGIN_VAR", PIMCORE_WEBSITE_PATH . "/var/plugins/YubiKey");


class YubiKey_Plugin extends Pimcore_API_Plugin_Abstract implements Pimcore_API_Plugin_Interface {
    
	public static function install (){
      if (!is_dir(YUBIKEY_PLUGIN_VAR)) mkdir(YUBIKEY_PLUGIN_VAR);

      foreach (array("users.xml", "config.xml") as $config_file) {
        if (!file_exists(YUBIKEY_PLUGIN_VAR.DIRECTORY_SEPARATOR.$config_file)) {
          copy(YUBIKEY_PLUGIN_PATH.DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR.$config_file, YUBIKEY_PLUGIN_VAR.DIRECTORY_SEPARATOR.$config_file);
        }

      }

      if (self::isInstalled()) {
        return "YubiKey Plugin successfully installed.";
      } else {
        return "YubiKey Plugin could not be installed";
      }

	}
	
	public static function uninstall (){

      if (!self::isInstalled()) {
        return "YubiKey Plugin successfully uninstalled.";
      } else {
        return "YubiKey Plugin could not be uninstalled";
      }
	}

	public static function isInstalled () {
      if (!is_dir(YUBIKEY_PLUGIN_PATH)) return false;
      if (!is_file(YUBIKEY_PLUGIN_VAR."/config.xml")) return false;
      return true;
	}

  /**
   * Hook called when login in pimcore is about to fail. Must return
   * a valid pimcore User for successful authentication or null for failure.
   *
   * @param string $username username provided in login credentials
   * @param string $password password provided in login credentials
   * @return User authenticated user or null if login shall fail
   * @deprecated
   */
  public function authenticateUser($username, $password)  {

    return YubiKey_Authenticator::authenticate($username, $password);

  }

  /**
   *
   * @param string $language
   * @return string path to the translation file relative to plugin direcory
   */
  public static function getTranslationFile($language) {
    if(file_exists(YUBIKEY_PLUGIN_PATH . "/texts/" . $language . ".csv")){
      return "/YubiKey/texts/" . $language . ".csv";
    }
    return "/YubiKey/texts/de.csv";
  }

}
if (version_compare(Pimcore_Version::$version, "2.2.0", ">=")) {
  Pimcore::getEventManager()->attach("admin.login.login.failed", function ($event) {

    $username = $event->getParam("username");
    $password = $event->getParam("password");

    $user = YubiKey_Authenticator::authenticate($username, $password);
    if ($user instanceof User) {
      $event->getTarget()->setUser($user);
    }

  });
}