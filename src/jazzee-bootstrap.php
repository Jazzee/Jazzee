<?php
/**
 * Jazzee Bootstrap
 * @todo load localBootstrap without loading config multipe times
 * Defines usefull constants
 * Sets up LVC, Foundation, Theme
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license ../LICENSE
 * @package jazzee
 */
$src = realpath(__DIR__ . '/..');

require_once($src . '/lib/foundation/src/foundation.php');

$classLoader = new Doctrine\Common\ClassLoader('Jazzee', $src . '/src');
$classLoader->register();

$classLoader = new Doctrine\Common\ClassLoader('Entity', $src . '/src');
$classLoader->register();

//load the configuration and cache it
$jazzeeConfig = new \Jazzee\Configuration();

//The localbootstrap can overload any of the above 
//or by adding something to the LVC paths will load before the stuff below this
if($jazzeeConfig->getLocalBootstrap()) require_once $jazzeeConfig->getLocalBootstrap();

//Setup the Lvc options

\Foundation\VC\Config::addControllerPath(__DIR__ . '/controllers/');
\Foundation\VC\Config::addControllerViewPath(__DIR__ . '/views/');
\Foundation\VC\Config::addLayoutViewPath(__DIR__ . '/views/layouts/');
\Foundation\VC\Config::addElementViewPath(__DIR__ . '/views/elements/');
\Foundation\VC\Config::addElementViewPath(__DIR__ . '/views/page_type_elements/');

\Foundation\VC\Config::addElementViewPath(__DIR__ . '/../lib/foundation/src/elements/');

//Load apply controllers and views
\Foundation\VC\Config::addControllerPath(__DIR__ . '/controllers/apply/');
\Foundation\VC\Config::addControllerViewPath(__DIR__ . '/views/apply/');

//Load admin controllers and views
\Foundation\VC\Config::addControllerPath(__DIR__ . '/controllers/admin/');
\Foundation\VC\Config::addControllerViewPath(__DIR__ . '/views/admin/');

//Load manage controllers and views
\Foundation\VC\Config::addControllerPath(__DIR__ . '/controllers/manage/');
\Foundation\VC\Config::addControllerViewPath(__DIR__ . '/views/manage/');

//Load setup controllers and views
\Foundation\VC\Config::addControllerPath(__DIR__ . '/controllers/setup/');
\Foundation\VC\Config::addControllerViewPath(__DIR__ . '/views/setup/');

//Load applicants controllers and views
\Foundation\VC\Config::addControllerPath(__DIR__ . '/controllers/applicants/');
\Foundation\VC\Config::addControllerViewPath(__DIR__ . '/views/applicants/');

\Foundation\VC\Config::setViewClassName('\Jazzee\View');
\Foundation\VC\Config::setDefaultControllerName('apply_welcome');
\Foundation\VC\Config::setDefaultControllerActionName('index');

//loading common scripts, themes, and styles
//Resource::getInstance()->addDirectory(APP_ROOT . '/common/scripts', 'common/scripts/', true);
//Resource::getInstance()->addDirectory(APP_ROOT . '/common/styles', 'common/styles/', true);
//Resource::getInstance()->addDirectory(APP_ROOT . '/common/media', 'common/media/', true);

/*
//add the builtin admin controller directories
AdminController::addControllerPath(APP_ROOT . '/controllers/manage');
AdminController::addControllerPath(APP_ROOT . '/controllers/setup');
AdminController::addControllerPath(APP_ROOT . '/controllers/applicants');
AdminController::addControllerPath(APP_ROOT . '/controllers/admin');
*/
?>
