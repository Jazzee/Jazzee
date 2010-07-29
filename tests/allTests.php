<?php
/**
 * Run all tests
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage test
 */
require_once('Jazzee_Test.php');
$test = &new TestSuite('Test all of Jazzee');

//add each test file
//$test->addTestFile('SomeTest.class.php');


//run all of the tests
if (TextReporter::inCli()){
  exit ($test->run(new textReporter()) ? 0 : 1); //on failures exit with a failure
}
$test->run(new HtmlReporter());

?>
