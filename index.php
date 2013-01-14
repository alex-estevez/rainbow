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

// Testing file manager
$oFiles = \Util\cFiles::create();
$oFiles->addLimit('size',1024);
$oFiles->checkLimits();

// Testing Debug tools
\Util\cDebug::add($oFiles);

// Testing PDO Db access /require correct SQL data and access credentials
$oDb = \Util\cPdo::create();
$result = $oDb->dbSelect('SELECT * FROM your_table');

\Util\cDebug::add($result);

?>
<html>
  <head>
    <title>Rainbow File Manager Test</title>
    <style>
      label { display: block; }
    </style>
  </head>
  <body>
    <form action="index.php" method="POST" enctype="multipart/form-data">
      <label>File to upload
        <input type="file" name="file1" />
      </label>
      <label>File to upload
        <input type="file" name="file2" />
      </label>
      <input type="submit" value="Upload" />
    </form>
    <div>
      <h1>Debug Info</h1>
      <?php echo \Util\cDebug::show(); ?>
      <h1>Debug Querys</h1>
      <?php echo \Util\cDebug::showQuerys(); ?>
    </div>
  </body>
</html>