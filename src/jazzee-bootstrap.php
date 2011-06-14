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
$classLoader = new Foundation\ClassLoader('Jazzee', $src . '/src');
$classLoader->register();

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


//add the builtin admin controller directories
\Jazzee\AdminController::addControllerPath(__DIR__ . '/controllers/manage');
\Jazzee\AdminController::addControllerPath(__DIR__ . '/controllers/setup');
\Jazzee\AdminController::addControllerPath(__DIR__ . '/controllers/applicants');
\Jazzee\AdminController::addControllerPath(__DIR__ . '/controllers/admin');

?>
