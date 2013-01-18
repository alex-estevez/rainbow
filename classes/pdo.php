<?php

namespace Rainbow;

/**
 * Class that wraps the PDO PHP class
 * Allows database querying throw PDO
 *
 * Requires PHP 5.3  (for use of namespaces)
 * Requires Rainbow \Rainbow\cDebug (for debugging)
 *
 * @author Alex Estevez
 * @version 0.9
 */
class cPdo extends \PDO {

  /**
   * Self Instance of this Object
   * @var Object $instance
   */
  static private $instance;
         private $arrSqls      = array();
         private $arrTypeData  = array();
         private $arrTypeAssoc = array();
         private $cacheQuery;

	const DATA       = 'data';
	const ASSOC      = 'assoc';
	const ALL_FIELDS = '*';

  /**
   * Constructor of the class.
   * Is public by PDO requirements. But it's convenient do class instantation
   * through create method.
   *
   * @param string $db_lib Type of Database for DSN construction
   * @param string $db_host Hostname for DSN construction
   * @param string $db_name database name
   * @param string $db_user user for the connection
   * @param string $db_pass password of this user/connection
   * @param array $db_options array of options to pass to the Database connection
   */
  public function __construct($db_lib = BD_TYPE, $db_host = BD_HOST, $db_name = BD_NAME, $db_user = BD_USER, $db_pass = BD_PASSW, $db_options = array()) {
    if(empty($db_options)) $db_options = array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
    try {
      parent::__construct("$db_lib:host=$db_host;dbname=$db_name", $db_user, $db_pass, $db_options);
    } catch (\PDOException $e) {
      \Rainbow\cDebug::add($e->getMessage(),'Error Connection to the Database');
      echo \Rainbow\cDebug::show();
      die();
    }
    if(defined('CACHE_QUERY')) $this->cacheQuery = CACHE_QUERY;
      else $this->cacheQuery = false;
  }

  /**
   * Return the object Instance, creating if not exists (Singleton)
   * Allows to use as singleton creator, and also as fluent acces to this class.
   *
   * <pre><code>
   * // Singleton example:
   * $oDb = $oDb = \Rainbow\cPdo::db();
   * // Fluent example
   * $result = \Rainbow\cPdo::db()->dbSelect('SELECT * FROM table');
   * </code></pre>
   * @return obj Instance
   */
  public static function db() {
    if( self::$instance == null ) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Close the connection
   * in PDO, the process is done just 'losing' the instance.
   */
  public static function disconnect() {
    self::$instance = NULL;
  }

  /**
   * Execute Sql Query.
   * First do prepare of it. Then executes.
   * @param string The Sql sentence
   * @return mixed The query result. (Depends on Query)
   */
  public function dbExec($sql) {
    \Rainbow\cDebug::startQuery();
		$data = $this->prepare($sql);
		$data->exec($sql);
    \Rainbow\cDebug::endQuery($sql,$data->errorCode());
		return $result;
  }

  /**
   * Executes a SQL Query that return rows, Paging them
   * @param string $sql    SQL Query string
   * @param int    $limit  [Optional] Default 10. Sets the maximum number of of rows to retrieve
   * @param int    $page   [Optional] Default 1. Sets the page to retrive. Pages calculated by $limit
   * @param string $fields [Optional] Default '*' (All). Set the fields to be retrieved.
   * @param const  $mode   [Optional] Default DATA.  Mode of retrieving. Could be [ \Rainbow\cPdo::DATA | \Rainbow\cPdo::ASSOC ]
   * @return array (
   *   array $Results the SQL retrieved rows array.
   *   int $num_pag The number of pages
   *   int $total Total of rows
   *   int $start First element retrieved position
   *   int $starting First element retrieved adjusted position
   *   int $finishing Last element retrieved adjusted position
   * )
   */
  public function dbSelectPaged ($sql, $limit = 10, $pag = 1, $field = \Rainbow\cPdo::ALL_FIELDS, $mode = \Rainbow\cPdo::DATA) {
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

  /**
   * Executes a SQL Query that return rows.
   *
   * @param string $sql    SQL Query string
   * @param string $fields [Optional] Default '*' (All). Set the fields to be retrieved.
   * @param const  $mode   [Optional] Default DATA.  Mode of retrieving. Could be [ \Rainbow\cPdo::DATA | \Rainbow\cPdo::ASSOC ]
   * @return array $Results the SQL retrieved rows array.
   */
  public function dbSelect($sql, $field = \Rainbow\cPdo::ALL_FIELDS, $mode = \Rainbow\cPdo::DATA) {
    $index = array_search($sql,$this->arrSqls);

    if($index !== FALSE AND $this->cacheQuery) {
      if($mode==\Rainbow\cPdo::DATA) {
        return $this->arrTypeData[$index];
      } elseif($mode==\Rainbow\cPdo::ASSOC) {
        return 	$this->arrTypeAssoc[$index];
      }
    } else {
      $index=array_push($this->arrSqls, $sql)-1;
    }

    $result = null;

    \Rainbow\cDebug::startQuery();
    $data = $this->prepare($sql);
    $data->execute();
    \Rainbow\cDebug::endQuery($sql,$data->errorCode());

		if($mode==\Rainbow\cPdo::DATA) {
			$v = $data->fetch(\PDO::FETCH_ASSOC);
			if($field==\Rainbow\cPdo::ALL_FIELDS) {
				$result = $v;
			} else {
				$result = $v[$field];
			}
			$this->arrTypeData[$index] = $result;
		} elseif($mode==\Rainbow\cPdo::ASSOC) {
		  if($field==\Rainbow\cPdo::ALL_FIELDS) {
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

  /**
   * Return the number of rows returned by a SQL quey
   * @param string $sql The SQL query
   * @return int The row count.
   */
  public function dbCount($sql) {
    $data = $this->prepare($sql);
    $data->execute();
    return $data->rowCount();
  }

}