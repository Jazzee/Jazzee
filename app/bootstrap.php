<?php
/**
 * Jazzee Bootstrap
 * Defines usefull constants
 * Sets up LVC, Foundation, Theme
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
define('SRC_ROOT', realpath(dirname(__FILE__) . '/..'));
define('APP_ROOT', SRC_ROOT . '/app');
define('WWW_ROOT',rtrim(dirname($_SERVER['SCRIPT_NAME']),'/\\.')); //strip any slashes and if this is the root directory a period (.) is possible
require_once(SRC_ROOT . '/lib/foundation/bootstrap.php');

Autoload::addAutoLoadPath(APP_ROOT . '/classes/');
Autoload::addAutoLoadPath(APP_ROOT . '/models/');

//Setup the Lvc options
Lvc_Config::addControllerPath(APP_ROOT . '/controllers/');
Lvc_Config::addControllerViewPath(APP_ROOT . '/views/');
Lvc_Config::addLayoutViewPath(APP_ROOT . '/views/layouts/');
Lvc_Config::addElementViewPath(APP_ROOT . '/views/elements/');

Lvc_Config::addElementViewPath(SRC_ROOT . '/lib/foundation/classes/forms/lvc_elements/');
Lvc_Config::addElementViewPath(SRC_ROOT . '/lib/foundation/classes/navigation/lvc_elements/');
Lvc_Config::addElementViewPath(SRC_ROOT . '/lib/foundation/classes/lvc_elements/');

//Load apply controllers and views
Lvc_Config::addControllerPath(APP_ROOT . '/controllers/apply/');
Lvc_Config::addControllerViewPath(APP_ROOT . '/views/apply/');

//Load admin controllers and views
Lvc_Config::addControllerPath(APP_ROOT . '/controllers/admin/');
Lvc_Config::addControllerViewPath(APP_ROOT . '/views/admin/');

//Load manage controllers and views
Lvc_Config::addControllerPath(APP_ROOT . '/controllers/manage/');
Lvc_Config::addControllerViewPath(APP_ROOT . '/views/manage/');

//Load setup controllers and views
Lvc_Config::addControllerPath(APP_ROOT . '/controllers/setup/');
Lvc_Config::addControllerViewPath(APP_ROOT . '/views/setup/');

//Load applicants controllers and views
Lvc_Config::addControllerPath(APP_ROOT . '/controllers/applicants/');
Lvc_Config::addControllerViewPath(APP_ROOT . '/views/applicants/');

Lvc_Config::setViewClassName('JazzeeView');
Lvc_Config::setDefaultControllerName('apply_welcome');
Lvc_Config::setDefaultControllerActionName('index');

//loading common scripts, themes, and styles
Resource::getInstance()->addDirectory(APP_ROOT . '/common/scripts', 'common/scripts/', true);
Resource::getInstance()->addDirectory(APP_ROOT . '/common/styles', 'common/styles/', true);
Resource::getInstance()->addDirectory(APP_ROOT . '/common/media', 'common/media/', true);


//Allow everything to be overridden
//load configuration file
$c = new Config;
$root = $c->parseConfig(SRC_ROOT . '/etc/config.ini.php', 'INICommented'); 
if (!PEAR::isError($root)) {
  $arr = $root->toArray();
  if(isset($arr['root']['system']['overridePath'])){
    $overridePath = $arr['root']['system']['overridePath'];
    if(is_dir($overridePath . '/classes')) Autoload::addAutoLoadPath($overridePath . '/classes/', true);
    if(is_dir($overridePath . '/controllers')) Lvc_FoundationConfig::prefixControllerPath($overridePath . '/controllers/');
    if(is_dir($overridePath . '/views')) Lvc_FoundationConfig::prefixControllerViewPath($overridePath . '/views/');
    if(is_dir($overridePath . '/elements')) Lvc_FoundationConfig::prefixElementViewPath($overridePath . '/elements/');
  }
  
  $theme = $arr['root']['system']['theme'];
  $theme_name = $theme;
  //attempting to load theme specific resources. CAN potentially overwrite default resources.
  if(strcasecmp($theme, "default") != 0) {
     //expected path root for storing theme specific files. Can be changed if necessary
     $styles_path = APP_ROOT . '/common/styles/';
     $images_path = APP_ROOT . '/common/images/';
     $scripts_path = APP_ROOT . '/common/scripts/';
     
     //theme styles, if exists.
     if (is_dir($styles_path . $theme)) {
        Resource::getInstance()->addDirectory($styles_path . $theme, 'common/styles/');
     }
     
     //theme images, if exists.
     if (is_dir($images_path . $theme)) {
        Resource::getInstance()->addDirectory($images_path . $theme, 'common/images/');
     }
     
     //theme scripts, if exists.
     if (is_dir($scripts_path . $theme)) {
        //Resource::getInstance()->addDirectory($scripts_path . $theme, 'common/scripts/');
     }
     
     if (!file_exists(APP_ROOT . '/views/layouts/' . $theme_name . ".php")) {
     	$theme_name = 'default'; 
     }
  }
  define('THM_NAME', $theme_name);
}
?>