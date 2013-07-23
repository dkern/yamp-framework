<?php
// error reporting
error_reporting(E_ALL | E_STRICT);

define("YAMP_ROOT", getcwd());

$coreFiles = array();
$coreFiles["config"]   = "/config.php";
$coreFiles["registry"] = "/core/Registry.php";
$coreFiles["core"]     = "/core/Yamp.php";

foreach( $coreFiles as $name => $file )
{
	if( !file_exists(YAMP_ROOT . $file) )
		die($name . " file '" . $file . "' not found");

	require_once(YAMP_ROOT . $file);
}

// may enable debug mode
// yamp::setDebugMode(true);

// start framework
yamp::run();

// print profiler data
Profiler::printData();
