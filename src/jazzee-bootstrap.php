<?php

/**
 * Jazzee Bootstrap
 *
 * Load all of the Views, Controllers, and Elements
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
//If the composer autoloader hasn't been loaded then load it here
//We do it this way in case Jazzee has been built as a composer app into another app
if (!class_exists('Composer\\Autoload\\ClassLoader', false)) {
  require __DIR__ . '/../vendor/autoload.php';
}


//Setup the Lvc options
\Foundation\VC\Config::addControllerPath(__DIR__ . '/controllers/');
\Foundation\VC\Config::addControllerViewPath(__DIR__ . '/views/');
\Foundation\VC\Config::addLayoutViewPath(__DIR__ . '/views/layouts/');
\Foundation\VC\Config::addElementViewPath(__DIR__ . '/views/elements/');

\Foundation\VC\Config::addElementViewPath(\Foundation\Configuration::getSourcePath() . '/src/elements/');

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

//Load scores controllers and views
\Foundation\VC\Config::addControllerPath(__DIR__ . '/controllers/scores/');
\Foundation\VC\Config::addControllerViewPath(__DIR__ . '/views/scores/');

\Foundation\VC\Config::setViewClassName('\Jazzee\View');
\Foundation\VC\Config::setDefaultControllerName('error');
\Foundation\VC\Config::setDefaultControllerActionName('index');
\Foundation\VC\Config::setDefaultControllerActionParams(array('error' => 404, 'message' => 'Sorry the page you are looking for could not be found.'));


//add the builtin admin controller directories
\Jazzee\AdminController::addControllerPath(__DIR__ . '/controllers/manage');
\Jazzee\AdminController::addControllerPath(__DIR__ . '/controllers/setup');
\Jazzee\AdminController::addControllerPath(__DIR__ . '/controllers/scores');
\Jazzee\AdminController::addControllerPath(__DIR__ . '/controllers/applicants');
\Jazzee\AdminController::addControllerPath(__DIR__ . '/controllers/admin');