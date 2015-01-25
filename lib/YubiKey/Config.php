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

  private $defaults = array();

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
      Logger::log("Problems with config file \"".YUBIKEY_PLUGIN_VAR.DIRECTORY_SEPARATOR."config.xml\": ".$e->getMessage());
      $this->config = $this->defaults;
    }
  }

  /**
   * @return array
   */
  public function getData() {
    return $this->config;
  }

  /**
   * @param array $data
   */
  public function setData($data) {
    $this->config = $data;
  }

  /**
   * @throws \Zend_Config_Exception
   */
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

  /**
   * @param $original
   * @param $array
   * @return mixed
   */
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