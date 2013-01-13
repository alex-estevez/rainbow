<?php

namespace Db;

class cPdo extends \PDO {
  
  static private $instance;
	private $arrSqls      = array();
	private $arrTypeData  = array();
	private $arrTypeAssoc = array();
	private $cacheQuery;
	
	const DATA       = 'data';
	const ASSOC      = 'assoc';
	const ALL_FIELDS = '*';
  
  public function __construct($db_lib = BD_TYPE, $db_host = BD_HOST, $db_name = BD_NAME, $db_user = BD_USER, $db_pass = BD_PASSW, $db_options = array()) {
    parent::__construct("$db_lib:host=$db_host;dbname=$db_name", $db_user, $db_pass, $db_options);
    if(defined('CACHE_QUERY')) $this->cacheQuery = CACHE_QUERY;
      else $this->cacheQuery = false;
  }
  
  public static function create() {
    if( self::$instance == null ) {
      self::$instance = new self();
    }
    return self::$instance;
  }
  public static function disconnect() {
    self::$instance = NULL;
  }
  
  public function dbExec($sql) {
    \Util\cDebug::prepareQuery();
		$result = $this->prepare($sql);
    \Util\cDebug::queryPrepared($sql,$result);
		$result = $this->exec($sql);
		return $result;
  }
    
  public function dbSelectPaged ($sql, $limit = 10, $pag = 1, $field = \Db\cPdo::ALL_FIELDS, $mode = \Db\cPdo::DATA) {
    $total   = $this->dbCount($sql);
    $num_pag = ceil($total/$limit);
		$start   = ($pag-1)* $limit;
    
		if($total>0) {
			$starting = $start+1;
		} else {
			$starting = 0;
		}
    
		if($total>0) {
			$finishing = 0;
		} elseif($pag==$num_pag) {
			$finishing = $total;
		} else {
			$finishing = $starting + $limit -1;
		}
    
		$sql .= " LIMIT $start,$limit ";
    $result = $this->dbSelect($sql, $field, $mode);
    return array($result, $num_pag, $total, $start, $starting, $finishing);
  }
    
  public function dbSelect($sql, $field = \Db\cPdo::ALL_FIELDS, $mode = \Db\cPdo::DATA) {
    $index = array_search($sql,$this->arrSqls);
    
    if($index !== FALSE AND $this->cacheQuery) {
      if($mode==\Db\cPdo::DATA) {
        return $this->arrTypeData[$index];
      } elseif($mode==\Db\cPdo::ASSOC) {
        return 	$this->arrTypeAssoc[$index];
      }
    } else {
      $index=array_push($this->arrSqls, $sql)-1;
    }
    
    $result = null;
    
    \Util\cDebug::prepareQuery();
    $data = $this->prepare($sql);
    $data->execute();
    \Util\cDebug::queryPrepared($sql,$result);
    
		if($mode==\Db\cPdo::DATA) {
			$v = $data->fetch(\PDO::FETCH_ASSOC);
			if($field==\Db\cPdo::ALL_FIELDS) {
				$result = $v;
			} else {
				$result = $v[$field];
			}
			$this->arrTypeData[$index] = $result;
		} elseif($mode==\Db\cPdo::ASSOC) {
		  if($field==\Db\cPdo::ALL_FIELDS) {
		    while($v = $data->fetch(\PDO::FETCH_ASSOC)) {
					$result[] = $v;
				}
			} else {
		    while($v = $data->fetch(\PDO::FETCH_ASSOC)) {
					$result[] = $v[$field];
				}
			}
			$this->arrTypeAssoc[$index] = $result;
    }

  	return $result;
	}
	
  public function dbCount($sql) {
    $data = $this->prepare($sql);
    $data->execute();
    return $data->rowCount();
  }
  
}