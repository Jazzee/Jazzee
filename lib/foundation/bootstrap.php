<?php
/**
 * Foundation Bootstrap
 * Checks PHP version
 * Checks for required PEAR Log and Email
 * Sets up LightVC, Doctrine, PHPass
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 */
 
//check dependencies
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    trigger_error('You are using PHP version ' . PHP_VERSION . '.  We require PHP version 5.3.0 or higher.',E_USER_ERROR);
}
$requiredIncludes = array('Log.php','Mail.php', 'Config.php');

foreach(explode(PATH_SEPARATOR, get_include_path()) as $dir){
  foreach($requiredIncludes as $file){
    if(file_exists($dir . '/' . $file)){
      include_once($file);
    }
  }
}
if(!class_exists('Log')){
  trigger_error('Pear Log is required and it is not availalble', E_USER_ERROR);
}
if(!class_exists('Mail')){
  trigger_error('Pear Mail is required and it is not availalble', E_USER_ERROR);
}
if(!class_exists('Config')){
  trigger_error('Pear Config is required and it is not availalble', E_USER_ERROR);
}
if(!class_exists('imagick')){
  trigger_error('PECL/Imagick is required and it is not availalble', E_USER_ERROR);
}

//load the helper functions
require_once('functions.php');

//load Doctrine infront of autoload
require_once('lib/doctrine/lib/Doctrine.php');
spl_autoload_register(array('Doctrine', 'autoload'));
//model autoloading is sepearte from doctrine component autoloading
spl_autoload_register(array('Doctrine_Core', 'modelsAutoload'));

require_once('classes/Autoload.class.php');

//add the classes directory to the autoload path
Autoload::addAutoLoadPath(dirname(__FILE__) . '/classes/');

//load lightVC
require_once('lib/lightvc/lightvc.php');

//load the portable password hasher
Autoload::addAutoLoadPath(dirname(__FILE__) . '/lib/phpass/');


$manager = Doctrine_Manager::getInstance();
$manager->setAttribute(Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL);
$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_CONSERVATIVE);
//allow the get/set accessors to be overridden
$manager->setAttribute(Doctrine::ATTR_AUTO_ACCESSOR_OVERRIDE, true);

//if apc is loaded us it to cache querys and results
if(extension_loaded('apc')){
  $cacheDriver = new Doctrine_Cache_Apc();
  $manager->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE, $cacheDriver);
  $manager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE, $cacheDriver);
}

//Create virtual paths to foundation resources
$virtualResources = Resource::getInstance();
$virtualResources->addDirectory(dirname(__FILE__) . '/javascript/', 'foundation/scripts');
$virtualResources->addDirectory(dirname(__FILE__) . '/media/famfamfam_silk_icons_v013/icons', 'foundation/icons/');

$virtualResources->add(dirname(__FILE__) . '/lib/jquery/jquery-1.4.2.min.js', 'foundation/scripts/jquery.js');
$virtualResources->add(dirname(__FILE__) . '/lib/jquery/plugins/jquery.json-2.2.min.js', 'foundation/scripts/jquery.json.js');
$virtualResources->add(dirname(__FILE__) . '/lib/jquery/plugins/jquery.cookie-1.min.js', 'foundation/scripts/jquery.cookie.js');
$virtualResources->add(dirname(__FILE__) . '/lib/jquery/jquery-ui-1.8.min.js', 'foundation/scripts/jqueryui.js');
$virtualResources->addDirectory(dirname(__FILE__) . '/lib/jquery/themes/', 'foundation/styles/jquery/themes');
$virtualResources->add(dirname(__FILE__) . '/lib/yui/base-min.css', 'foundation/styles/base.css');
$virtualResources->add(dirname(__FILE__) . '/lib/yui/reset-fonts-grids-min.css', 'foundation/styles/reset-fonts-grids.css');

?>
