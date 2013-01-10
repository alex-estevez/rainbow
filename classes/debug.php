<?php

namespace Util;

class cDebug {
  
  static private $instance;
  static private $debugInfo;
  static private $on = false;
  
  private function __construct() {
    self::$debugInfo = '';
    if(defined('DEBUG')) self::$on = true;
  }
  
  static private function create() {
    $c = __CLASS__;
    self::$instance = new $c;
  }
  
  static public function add($element) {
    if (!isset(self::$instance)) self::create();
    if (self::$on===false) return true;
    self::$debugInfo .= print_r($element,true)."\n";
  }

  static public function show() {
    if (!isset(self::$instance)) self::create();
    if (self::$on===false) return true;
    echo '<pre>'.self::$debugInfo."</pre>\n";
  }
  
}