<?php
/**
 * Routing for admin controllers
 * All the posible routes get mapped here and matched in the front controller
 */
$fc = new \Foundation\VC\FrontController();

$basicRouter = new Lvc_RegexRewriteRouter;

//dynmaic files like applicant pdfs and previews stored in sessions
$basicRouter->addRoute('#^(?:.*)/?virtualfile/(.*)$#i', array( 
  'controller' => 'virtualfile',
  'action' => 'get',
  'action_params' => array(
    'name' => 1
  )
));

//resources in the virtual file system
$basicRouter->addRoute('#^(?:.*)/?resource/(.*)$#i', array( 
  'controller' => 'resource',
  'action' => 'get',
  'action_params' => array(
    'path' => 1
  )
));

//dynmaic files like applicant pdfs and previews stored in sessions
$basicRouter->addRoute('#^(?:.*)/?file/(.*)$#i', array( 
  'controller' => 'file',
  'action' => 'get',
  'action_params' => array(
    'name' => 1
  )
));
  
//static pulls physical files from the cache where they are created when first needed
$basicRouter->addRoute('#^static/(.*)$#i', array( 
  'controller' => 'static',
  'action' => 'get',
  'action_params' => array(
    'fileName' => 1
  )
));

//anything with a trailing slash gets redirected without it
//this should be done with modrewrite so we get a permanent redirect, but it is here in case
//modrewrite isn't available
$basicRouter->addRoute('#(.*)/$#', array( 
  'redirect' => 'index.php?url=$1'
));

//default controller
$basicRouter->addRoute('#^$#i', array(
  'redirect' => 'welcome'
));

//single applicant view
$basicRouter->addRoute('#^applicants/single/([0-9]+)/?([^/]+)?/?(.*)$#i', array(
  'controller' => 'applicants_single',
  'action' => 2,
  'action_params' => array(
    'applicantId' => 1
  ),
  'additional_params' => 3
));

$fc->addRouter($basicRouter);




//We use preg replace in the admin routers to cleanly group the administrative responsiblities in the URL
$advancedRouter = new \Foundation\VC\FullRegexRewriteRouter();

$advancedRouter->addRoute('#^(manage|setup|applicants|scores)/([^/]+)/?([^/]*)/?(.*)$#i', array(
  'controller' => '$1_$2',
  'action' => '$3',
  'additional_params' => '$4'
));

$advancedRouter->addRoute('#^([^/]+)/?([^/]+)?$#i', array(
  'controller' => 'admin_$1',
  'action' => '$2'
));

$fc->addRouter($advancedRouter);

$fc->processRequest(new Lvc_HttpRequest());