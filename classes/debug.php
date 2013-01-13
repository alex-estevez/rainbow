<?php

namespace Util;

/**
 * Class to do debbuging task.
 * Allows to put Debug code anywhere without taking care of ambits,
 * locals, globalts or other context premises.
 * Also skips you of taking care of instantiation.
 * 
 * Requires PHP 5.3  (for use of namespaces)
 * 
 * @author Alex Estevez
 * @version 0.1
 */
class cDebug {
  
  /**
   * Self Instance of this Object 
   * @var Object $instance 
   */
  static private $instance;
  
  /**
   * String to save the Debug info.
   * @var string $debugInfo
   */
  static private $debugInfo;
  
  /**
   * String to save the Database Query Debug info.
   * @var string $debugQueryInfo
   */
  static private $debugQueryInfo;
  static private $queryStart;
  static private $queryEnd;
  
  /**
   * Flag to know id the class is Instantiated and Active
   * @var bool $on
   */
  static private $on = false;

  /**
   * Class Constructor (private for external managing)
   * Initializes the Debug Object. Requires DEBUG constant
   * to be defined. Otherwise Debug will be disabled.
   */
  private function __construct() {
    self::$debugInfo = '';
    self::$debugQueryInfo = '';
    if(defined('DEBUG')) self::$on = true;
  }
  
  /**
   * Creates the Instance (and calls the private constructor)
   * keeping the instance on static property $instance
   */
  static private function create() {
    $c = __CLASS__;
    self::$instance = new $c;
  }
  
  /**
   * Add Debug information
   *  - check if Debug if instantiated and active. If not, do nothing
   *  - Save internally the  print_r($element) result.
   * @param mix $element Element to Debug
   * @return Debug status (true : active | false: inactive )
   * @see print_r();
   */
  static public function add($element) {
    if (!isset(self::$instance)) self::create();
    if (self::$on!==false) self::$debugInfo .= print_r($element,true)."\n";
    return self::$on;
  }

  /**
   * Add Debug information About a Database Query
   *  - check if Debug if instantiated and active. If not, do nothing
   *  - Save internally the  print_r($element) result.
   * @param string $sql The sql statement
   * @return Debug status (true : active | false: inactive )
   */
  static public function prepareQuery() {
    if (!isset(self::$instance)) self::create();
    self::$queryStart = microtime(true);
    return self::$on;
  }
  static public function queryPrepared($sql,$result) {
    if (!isset(self::$instance)) return false;
    $time = microtime(true)-self::$queryStart;
    if($result===false)self::$debugQueryInfo .= 'FAIL: ';
    self::$debugQueryInfo .= "$sql ($time)\n";
    return self::$on;
  }


  /**
   * Returns a string with all the recopiled Debug Data
   * @return string The debug string content
   */
  static public function show() {
    if (!isset(self::$instance)) self::create();
    if (self::$on===false) return '';
    return '<pre>'.self::$debugInfo."</pre>\n";
  }
  
  /**
   * Returns a string with all the recopiled Debug Data
   * @return string The debug string content
   */
  static public function showQuerys() {
    if (!isset(self::$instance)) self::create();
    if (self::$on===false) return '';
    return '<pre>'.self::$debugQueryInfo."</pre>\n";
  }
  
}