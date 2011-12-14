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
  
//static pulls physical files from the cache where they are created when first needed
$basicRouter->addRoute('#^static/(.*)$#i', array( 
  'controller' => 'static',
  'action' => 'get',
  'action_params' => array(
    'fileName' => 1
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
    'id' => 4,
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
$basicRouter->addRoute('#^apply/([^/]+)/([^/]+)/status/do/([^/]+)/([0-9]+)/?([0-9]+)?$#i', array(
  'controller' => 'apply_status',
  'action' => 'do',
  'action_params' => array(
    'programShortName' => 1,
    'cycleName' => 2,
    'what' => 3,
    'pageId' => 4,
    'answerId' => 5
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

//default controller
$basicRouter->addRoute('#^admin$#i', array(
  'redirect' => 'admin/welcome'
));

//single applicant view
$basicRouter->addRoute('#^admin/applicants/single/([0-9]+)/?([^/]+)?/?(.*)$#i', array(
  'controller' => 'applicants_single',
  'action' => 2,
  'action_params' => array(
    'applicantId' => 1
  ),
  'additional_params' => 3
));

//transactions come as posts from outside sources
$basicRouter->addRoute('#^transaction/(.*)$#i', array( 
  'controller' => 'transaction',
  'action' => 'post',
  'action_params' => array(
    'name' => 1
  )
));

$fc->addRouter($basicRouter);

//We use preg replace in the admin routers to cleanly group the administrative responsiblities in the URL
$advancedRouter = new \Foundation\VC\FullRegexRewriteRouter();

$advancedRouter->addRoute('#^admin/(manage|setup|applicants|scores)/([^/]+)/?([^/]*)/?(.*)$#i', array(
  'controller' => '$1_$2',
  'action' => '$3',
  'additional_params' => '$4'
));

$advancedRouter->addRoute('#^admin/([^/]+)/?([^/]+)?$#i', array(
  'controller' => 'admin_$1',
  'action' => '$2'
));

$fc->addRouter($advancedRouter);

$fc->processRequest(new Lvc_HttpRequest());