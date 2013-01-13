<?php

namespace Util;

class cFiles {
  
  private $num_files;
  private $limits;
  private $files;

  static private $instance;
  
  private function __construct() {
    $this->num_files = count($_FILES);
    \Util\cDebug::add($_FILES);
    
  }
  
  static public function create() {
    if (!isset(self::$instance)) {
      $c = __CLASS__;
      self::$instance = new $c;
    }
    return self::$instance;
  }
  
  public function addLimit($name,$value) {
    $this->limits[$name] = $value;
  }
  
  public function checkLimits($files = null) {
    if(is_null($files)) $files = array_keys($_FILES);
      elseif(is_string($files)) $files = array($files);
    foreach($files as $file) {
      if(isset($_FILES[$file])) {
        $this->files[$file] = 0;
        foreach($this->limits as $key => $limit) {
          $limitchecker = 'checkLimit'.ucwords($key);
          $this->files[$file] += $this->$limitchecker($file,$limit);
        }
      }
    }
    \Util\cDebug::add($files);
  }
  
  private function checkLimitSize($file,$limit) {
    if($limit>$_FILES[$file]['size']) return 1;
    return 0;
  }
  
  public function getFileData($filename = null) {
    if($filename===null) var_dump($_FILES);
      else var_dump($_FILES[$filename]);
    
  }
  
  
}