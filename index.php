<?php
/**
 * Rainbow Class File Uploader.
 * File to test working.
 */

namespace Proyect;

define('DEBUG',1);
include('classes/rainbow.php');
include('classes/debug.php');

$oRainbow = \Rainbow\cRainbow::create();

$oRainbow->addLimit('size',1024);
$oRainbow->checkLimits();

\Util\cDebug::add($oRainbow);

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
      <?php \Util\cDebug::show(); ?>
    </div>
  </body>
</html>