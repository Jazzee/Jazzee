<?php
/**
 * Jazzee Test base class
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage test
 */
if (isset($_GET['simpleTestPath'])){
  define('SIMPLETEST_PATH', $_GET['simpleTestPath']);
} else {
  define('SIMPLETEST_PATH', $_SERVER['DOCUMENT_ROOT'] . '/../simpletest');
}
if (!file_exists(SIMPLETEST_PATH . '/autorun.php')){
  die('SIMPLE_TEST_PATH ' . SIMPLETEST_PATH . ' is incorect.  Specify the path with GET simpleTestPath or use the default WEBROOT/../simpletest');
}
define('SOURCE_PATH', realpath(dirname(__FILE__) . '/../'));
require_once(SIMPLETEST_PATH . '/autorun.php');
require_once(SIMPLETEST_PATH . '/mock_objects.php');

/**
 * unitTestCase for Jazzee
 * Subclasses the simpletest UnitTestCase to allow for the addition of complex
 * helper functions and assertions
 */
class Jazzee_Text extends UnitTestCase {}
?>
