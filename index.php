<?php
/**
 * Rainbow Utils.
 * File to test working.
 */

define('BD_TYPE',     'mysql');
define('DB_PREFIX',   '');
define('BD_HOST',     'localhost');
define('BD_NAME',     'dbname');
define('BD_USER',     'dbuser');
define('BD_PASSW',    'dbpass');
define('CACHE_QUERY', true);
define('DEBUG',       1);

// simple class autoloader only for test purposes.
function __autoload($class_name) {
  $class_parts = explode("\\",$class_name);
  $class_name = end($class_parts);
  $class_name = strtolower(substr($class_name,1));
  $class_path = 'classes/'.$class_name.'.php';
  if(file_exists($class_path)) include $class_path;
}

// Testing PDO Db access /require correct SQL data and access credentials

$result = \Rainbow\cPdo::db()->dbSelect("SELECT CONCAT('Hello') AS Greetings, CONCAT('Bye') AS Regards");
\Rainbow\cDebug::add($result,'Results of first Select');
$result = \Rainbow\cPdo::db()->dbSelect('SELECT WITH ERRORS');
\Rainbow\cDebug::add($result,'Results of second Select');

?>
<html>
  <head>
    <title>Rainbow Testing</title>
  </head>
  <body>
    <div>
      <h1>Debug Info</h1>
      <?php echo \Rainbow\cDebug::show(); ?>
      <h1>Debug Querys</h1>
      <?php echo \Rainbow\cDebug::showQuerys(); ?>
    </div>
  </body>
</html>    