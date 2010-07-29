<?php
//load Doctrine
require_once('../../lib/foundation/lib/doctrine/lib/Doctrine.php');
spl_autoload_register(array('Doctrine', 'autoload'));

$manager = Doctrine_Manager::getInstance();
$manager->setAttribute(Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL);
$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_CONSERVATIVE);

//allow the get/set accessors to be overridden
$manager->setAttribute(Doctrine::ATTR_AUTO_ACCESSOR_OVERRIDE, true);

//The name of the person generating the models is passed as the first argument on the cli
$name = !empty($argv[1])?$argv[1]:'';

//The email address of the person generating the models is passed as the second argument on the cli
$email = !empty($argv[2])?$argv[2]:'';

$options = array(
  'baseClassesDirectory' => 'base',
  'phpDocPackage' => 'jazzee',
  'phpDocSubpackage' => 'orm',
  'phpDocName' => $name,
  'phpDocEmail' => $email
);
Doctrine::generateModelsFromYaml('../schema/schema.yml', '../../app/models/doctrine/', $options);

print 'Models generated successfully';
?>
