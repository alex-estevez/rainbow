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
 * @version 1.0
 */
class cDebug {

  /**
   * Configure this to your preferences
   */

  /**
   * SQL Time in second for Warning
   */
  const queryWarn = 0.01;

  /**
   * SQL Time in second for Critical
   */
  const queryCrit = 0.05;

  /**
   * Color for OK SQL
   */
  const colorOkey = '#009900';

  /**
   * Color for Warning SQL
   */
  const colorWarn = '#996633';

  /**
   * Color for Critical SQL
   */
  const colorCrit = '#990000';

  /**
   * string to prepend before each item to debug
   */
  const itemStart = '<div style="border: solid 1px #cccccc; margin: 5px; padding: 5px;">';

  /**
   * string to add after each item to debug
   */
  const itemClose = '</div>';

  /**
   * Self Instance of this Object
   * @var Object $instance
   */
  static private $instance;

  /**
   * array to save the Debug info.
   * @var string $debugInfo
   */
  static private $debugInfo = array();

  /**
   * Array to save the Database Query Debug info.
   * @var string $debugQueryInfo
   */
  static private $debugQueryInfo = array();

  /**
   * Save start time
   * @var float queryStart
   */
  static private $queryStart;

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
   * Add Debug information Entry.
   * @param mix $element Element to Debug
   * @param string $description [Optional] Descriptive text of the Debug.
   */
  static public function add($element,$description = '') {
    if (!isset(self::$instance)) self::create();
      $debugInfo = debug_backtrace();
      self::$debugInfo[] = array(
         'desc' => $description
        ,'data' => print_r($element,true)
        ,'info' => 'Line '.str_pad($debugInfo[0]['line'], 5, "0", STR_PAD_LEFT).': '.$debugInfo[0]['file']
      );
  }

  /**
   * Check time to evaluate how much take the Query to execute
   * You have to call this method inmediatelly before executing the query.
   */
  static public function startQuery() {
    if (!isset(self::$instance)) self::create();
    self::$queryStart = microtime(true);
  }

  /**
   * Add Debug information about a Databse Query
   * You have to call this method inmediatelly after executing the query.
   * @param string $sql Exeuted SQL String Debbuged
   * @param int $errorCode Result code of the execution
   */
  static public function endQuery($sql,$errorCode) {
    if (!isset(self::$instance)) return false;
    $time = microtime(true)-self::$queryStart;
    if(self::queryCrit<$time) {
      $color = self::colorCrit;
    } elseif(self::queryWarn<$time) {
      $color = self::colorWarn;
    } else {
      $color = self::colorOkey;
    }

    if($errorCode!=00000) {
      $color = self::colorCrit;
      $state = 'FAIL';
    } else {
      $state = 'OKEY';
    }
    self::$debugQueryInfo[] = array(
       'state' => $state." ($errorCode)"
      ,'color' => $color
      ,'sql'   => $sql
      ,'time'  => $time
    );
  }

 /**
   * Returns a string with all the recopiled Debug Data
   * @return string The debug string content
   */
  static public function show() {
    if (!isset(self::$instance)) self::create();
    if (self::$on===false) return '';
    $content = '';
    foreach(self::$debugInfo as $item) {
      $content .= self::itemStart;
      if($item['desc']!='') $content .= '<b>'.$item['desc']."</b>\n";
      $content .= '<i>'.$item['info']."</i>\n";
      $content .= $item['data']."\n";
      $content .= self::itemClose;
    }
    return '<pre>'.$content."</pre>\n";
  }

  /**
   * Returns a string with all the recopiled Debug Data
   * @return string The debug string content
   */
  static public function showQuerys() {
    if (!isset(self::$instance)) self::create();
    if (self::$on===false) return '';
    $time = 0;
    $count = 0;
    $content = self::itemStart;
    foreach(self::$debugQueryInfo as $item) {
      $content .= '<span style="color: '.$item['color'].';"><b>'.$item['state'].':</b>'.$item['sql'].' ('.$item['time'].")</span>\n";
      $time += $item['time'];
      $count++;
    }
    $content .= "<b>Total: $count Querys. $time seconds.</b>\n".self::itemClose;
    return '<pre>'.$content."</pre>\n";
  }

}