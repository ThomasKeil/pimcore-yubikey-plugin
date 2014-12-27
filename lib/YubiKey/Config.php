<?php
/**
 * Created by PhpStorm.
 * User: thomas
 * Date: 28.11.13
 * Time: 09:02
 */

namespace YubiKey;

/**
 * Class YubiKey\Config
 *
 * Reads and creates the config file
 * located at website/var/plugins/YubiKey/config.xml
 */
class Config {

  /**
   * @var array
   */
  private $config;

  private $defaults = array(
  );

  /**
   * Singleton instance
   *
   * @var \YubiKey\Config
   */
  protected static $_instance = null;

  /**
   * Returns an instance of \YubiKey\Config
   *
   * Singleton pattern implementation
   *
   * @return \YubiKey\Config
   */
  public static function getInstance() {
    if (null === self::$_instance) {
      self::$_instance = new self();
    }

    return self::$_instance;
  }


  public function __construct() {
    try {
      $config = new \Zend_Config_Xml(YUBIKEY_PLUGIN_VAR.DIRECTORY_SEPARATOR."config.xml");
      $this->config = $config->toArray();
    } catch (\Zend_Config_Exception $e) {
      \Logger::error("Problems with config file \"".YUBIKEY_PLUGIN_VAR.DIRECTORY_SEPARATOR."config.xml\": ".$e->getMessage());
      $this->config = $this->defaults;
    }
  }

  /**
   * @return array
   */
  public function getData() {
    return $this->config;
  }


  public function setData($data) {
    $this->config = $data;
  }

  public function save() {
    $defaults = $this->defaults;
    $params = $this->getData();

    $data = $this->array_join($defaults, $params);

    $config = new \Zend_Config($data, true);
    $writer = new \Zend_Config_Writer_Xml(array(
      "config" => $config,
      "filename" => YUBIKEY_PLUGIN_VAR.DIRECTORY_SEPARATOR."config.xml"
    ));
    $writer->write();
  }

  private function array_join($original, $array) {
    foreach ($array as $key => $value) {
      if (is_array($value)) {
        $original[$key] = $this->array_join($original[$key], $array[$key]);
      } else {
        $original[$key] = $value;
      }
    }
    return $original;
  }

}