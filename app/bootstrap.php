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

//Allow everything to be overridden
//load configuration file
if(file_exists(SRC_ROOT . '/etc/config.ini.php')){
  $config = new IniConfigType(SRC_ROOT . '/etc/config.ini.php');
  if($config->readVar('localBootstrap') != '') require_once($config->readVar('localBootstrap'));
}

Autoload::addAutoLoadPath(APP_ROOT . '/classes/');
Autoload::addAutoLoadPath(APP_ROOT . '/models/');

//Setup the Lvc options
FoundationVC_Config::addControllerPath(APP_ROOT . '/controllers/');
FoundationVC_Config::addControllerViewPath(APP_ROOT . '/views/');
FoundationVC_Config::addLayoutViewPath(APP_ROOT . '/views/layouts/');
FoundationVC_Config::addElementViewPath(APP_ROOT . '/views/elements/');
FoundationVC_Config::addElementViewPath(APP_ROOT . '/views/page_type_elements/');

FoundationVC_Config::addElementViewPath(SRC_ROOT . '/lib/foundation/classes/forms/lvc_elements/');
FoundationVC_Config::addElementViewPath(SRC_ROOT . '/lib/foundation/classes/navigation/lvc_elements/');
FoundationVC_Config::addElementViewPath(SRC_ROOT . '/lib/foundation/classes/lvc_elements/');

//Load apply controllers and views
FoundationVC_Config::addControllerPath(APP_ROOT . '/controllers/apply/');
FoundationVC_Config::addControllerViewPath(APP_ROOT . '/views/apply/');

//Load admin controllers and views
FoundationVC_Config::addControllerPath(APP_ROOT . '/controllers/admin/');
FoundationVC_Config::addControllerViewPath(APP_ROOT . '/views/admin/');

//Load manage controllers and views
FoundationVC_Config::addControllerPath(APP_ROOT . '/controllers/manage/');
FoundationVC_Config::addControllerViewPath(APP_ROOT . '/views/manage/');

//Load setup controllers and views
FoundationVC_Config::addControllerPath(APP_ROOT . '/controllers/setup/');
FoundationVC_Config::addControllerViewPath(APP_ROOT . '/views/setup/');

//Load applicants controllers and views
FoundationVC_Config::addControllerPath(APP_ROOT . '/controllers/applicants/');
FoundationVC_Config::addControllerViewPath(APP_ROOT . '/views/applicants/');

FoundationVC_Config::setViewClassName('JazzeeView');
FoundationVC_Config::setDefaultControllerName('apply_welcome');
FoundationVC_Config::setDefaultControllerActionName('index');

//loading common scripts, themes, and styles
Resource::getInstance()->addDirectory(APP_ROOT . '/common/scripts', 'common/scripts/', true);
Resource::getInstance()->addDirectory(APP_ROOT . '/common/styles', 'common/styles/', true);
Resource::getInstance()->addDirectory(APP_ROOT . '/common/media', 'common/media/', true);

?>