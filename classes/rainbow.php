<?php

namespace Rainbow;

class cRainbow {

  static private $instance;
  
  private function __construct() {
    
  }
  
  static public function create() {
    if (!isset(self::$instance)) {
      $c = __CLASS__;
      self::$instance = new $c;
    }
    return self::$instance;
  }
}