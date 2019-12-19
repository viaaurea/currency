<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// runs a test (all tests) found in QUERY_STRING, displays a list of available tests otherwise
//
// Note: viaaurea/tester package is needed for this to work.


$selectedTest = filter_input(INPUT_SERVER, 'QUERY_STRING', FILTER_SANITIZE_STRING);
$extension = 'phpt';
$dir = '.';

require_once $dir . '/../vendor/autoload.php';

$runner = new VA\Tester\Runner($dir, $extension);

// find files with phpt extension
$availableTests = $runner->getTestNames();

$all = '4ll';
if (!empty($selectedTest) && $selectedTest !== $all) {
    // run the selected test
    $runner->runSingle($selectedTest);
} elseif ($selectedTest === $all) {
    // run all tests
    $runner->runAll();
} else {
    // print available test (links)
    print "<h1>Select a test to run</h1>";
    print "<fieldset><legend>Available tests:</legend><ul>";
    foreach ($availableTests as $filename) {
        print '<li><a href="?' . $filename . '">' . $filename . '</a></li>';
    }
    print '<li></li><li><a href="?' . $all . '">--- <i>run all ---</i></a></li>';
    print "</ul></fieldset>";
}

