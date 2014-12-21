<?php

if (!defined("YUBIKEY_PLUGIN_PATH")) define("YUBIKEY_PLUGIN_PATH", PIMCORE_PLUGINS_PATH."/YubiKey");
if (!defined("YUBIKEY_PLUGIN_VAR"))  define("YUBIKEY_PLUGIN_VAR", PIMCORE_WEBSITE_PATH . "/var/plugins/YubiKey");


class YubiKey_Plugin extends Pimcore_API_Plugin_Abstract implements Pimcore_API_Plugin_Interface {
    
	public static function install (){

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

	}
	
	public static function uninstall (){
        // implement your own logic here
        return true;
	}

	public static function isInstalled () {
        // implement your own logic here
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
