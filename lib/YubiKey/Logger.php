<?php
/**
 * Created by PhpStorm.
 * User: thomas
 * Date: 05.01.15
 * Time: 19:10
 */

namespace YubiKey;
use Pimcore\Log;

class Logger {
  public static function log($message) {
    self::_log($message);
  }
  
  public static function debug($message) {
    self::_log("Debug: ".$message);
  }

  public static function error($message) {
    self::_log("Error: ".$message);
  }

  private static function _log($message) {
    Log\Simple::log("YubiKey", "[Local Auth] ".$message);
  }
}