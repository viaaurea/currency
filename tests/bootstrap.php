<?php


namespace VA\Currency\Tests;

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT', __DIR__);

require_once ROOT . '/../vendor/autoload.php';

use Tracy\Debugger,
	Tester\Environment;

// tester + errors
Environment::setup();

// debugging
if(PHP_SAPI !== 'cli') {
	Debugger::$strictMode = true;
	Debugger::enable();
	Debugger::$maxDepth = 10;
	Debugger::$maxLength = 500;
}


// dump shortcut
function dump($var, $return = FALSE)
{
	return Debugger::dump($var, $return);
}
