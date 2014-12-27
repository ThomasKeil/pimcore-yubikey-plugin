<?php

namespace YubiKey;

class SettingsController extends \Pimcore\Controller\Action\Admin {

  public function settingsAction() {
    $config = Config::getInstance();
    $data = $config->getData();
    $this->_helper->json($data);
  }

  public function saveAction() {
    $values = \Zend_Json::decode($this->getParam("data"));

    // convert all special characters to their entities so the xml writer can put it into the file
    $values = array_htmlspecialchars($values);
    try {
      $config = Config::getInstance();
      $data = $config->getData();

      $new_data = array(
        "server" => $values["server"],
        "port" => $values["port"],
        "accesstoken" => $values["accesstoken"],
        "rsa" => array(
          "public" => $values["rsa.public"],
          "private" => $values["rsa.private"]
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
