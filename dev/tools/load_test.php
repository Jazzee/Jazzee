<?php
//load Doctrine
require_once('../../lib/foundation/lib/doctrine/lib/Doctrine.php');
spl_autoload_register(array('Doctrine', 'autoload'));

$manager = Doctrine_Manager::getInstance();
$manager->setAttribute(Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL);
$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_CONSERVATIVE);

//The path to a single yaml file or a directory of files is the first argument
if(empty($argv[1])){
  print 'Path to yaml file used to generate test is required'.
  die();
}

Doctrine_Core::loadData($argv[1]);


print "Demo database loaded\n";
?>
