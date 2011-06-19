<?php
/**
 * Routing for apply requests
 */
$fc = new \Foundation\VC\FrontController();

$basicRouter = new Lvc_RegexRewriteRouter;

//anything with a trailing slash gets redirected without it
//this should be done with modrewrite so we get a permanent redirect, but it is here in case
//modrewrite isn't available
$basicRouter->addRoute('#(.*)/$#', array( 
  'redirect' => 'index.php?url=$1'
));

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

//the apply page is the actual application
$basicRouter->addRoute('#^apply/([^/]+)/([^/]+)/page/([0-9]+)/?(?:(index|edit|delete)/([0-9]+)(/[0-9]+)?)?$#i', array(
  'controller' => 'apply_page',
  'action' => 4,
  'action_params' => array(
    'programShortName' => 1,
    'cycleName' => 2,
    'pageID' => 3,
    'answerID' => 5
  )
));

//special do type sends a second string to identify the method and an optional answerid
$basicRouter->addRoute('#^apply/([^/]+)/([^/]+)/page/([0-9]+)/do/([^/]+)/?([0-9]+)?$#i', array(
  'controller' => 'apply_page',
  'action' => 'do',
  'action_params' => array(
    'programShortName' => 1,
    'cycleName' => 2,
    'pageID' => 3,
    'what' => 4,
    'answerID' => 5
  )
));

//apply welcome handles requests until we know which application we are seeing
$basicRouter->addRoute('#^apply/?([^/]*)/?([^/]*)$#i', array(
  'controller' => 'apply_welcome',
  'action' => 'index',
  'action_params' => array(
    'programShortName' => 1,
    'cycleName' => 2
  )
));

//lor requests
$basicRouter->addRoute('#^lor/([^/]+)$#i', array(
  'controller' => 'lor',
  'action' => 'index',
  'action_params' => array(
    'urlKey' => 1
  )
));

//applicant support
$basicRouter->addRoute('#^apply/([^/]+)/([^/]+)/support/?([^/]+)?/?([0-9]+)?$#i', array(
  'controller' => 'apply_support',
  'action' => 3,
  'action_params' => array(
    'programShortName' => 1,
    'cycleName' => 2,
    'messageId' => 4,
  )
));

//applicant status
$basicRouter->addRoute('#^apply/([^/]+)/([^/]+)/status/?([^/]+)?$#i', array(
  'controller' => 'apply_status',
  'action' => 3,
  'action_params' => array(
    'programShortName' => 1,
    'cycleName' => 2
  )
));

//applicant status
$basicRouter->addRoute('#^apply/([^/]+)/([^/]+)/applicant/?([^/]+)?$#i', array(
  'controller' => 'apply_applicant',
  'action' => 3,
  'action_params' => array(
    'programShortName' => 1,
    'cycleName' => 2
  )
));

//applicant status
$basicRouter->addRoute('#^apply/([^/]+)/([^/]+)/applicant/resetpassword/([a-z0-9]+)$#i', array(
  'controller' => 'apply_applicant',
  'action' => 'resetpassword',
  'action_params' => array(
    'programShortName' => 1,
    'cycleName' => 2,
    'uniqueId' => 3
  )
));

$fc->addRouter($basicRouter);
$fc->processRequest(new Lvc_HttpRequest());