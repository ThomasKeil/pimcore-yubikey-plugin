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

class YubiKey_SettingsController extends \Pimcore\Controller\Action\Admin {

  /**
   * Reads the plugins settings
   */
  public function settingsAction() {
    $config = \YubiKey\Config::getInstance();
    $data = $config->getData();
    $this->_helper->json($data);
  }

  /**
   * Saves the plugins settings
   */
  public function saveAction() {
    try {
      $values = \Zend_Json::decode($this->getParam("data"));

      // convert all special characters to their entities so the xml writer can put it into the file
      $values = array_htmlspecialchars($values);
      $config = \YubiKey\Config::getInstance();
      $data = $config->getData();

      $new_data = array(
        "yubikey" => array(
          "local" => array(
              "uselocal" => $values["local_uselocal"] ? 1 : 0,
              "privatekey" => $values["local_privatekey"],
              "publickey" => $values["local_publickey"]
          ),
          "remote" => array(
              "useremote" =>  $values["remote_useremote"] ? 1 : 0,
              "server" => $values["remote_server"],
              "port" => $values["remote_port"],
              "usessl" =>  $values["remote_usessl"] ? 1 : 0,
              "publickey" => $values["remote_publickey"],
              "identifier" => $values["remote_identifier"]
          )
        )
      );

      $data = array_merge($data, $new_data);
      $config->setData($data);
      $config->save();
      $this->_helper->json(array("success" => true));
    } catch (\Exception $e) {
      $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
    }
  }
}
