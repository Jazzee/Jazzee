<?php
require_once('../app/bootstrap.php');
try {
  $fc = new Lvc_FrontController();
  //do the easy stuff here becuase it is faster
  $basicRouter = new Lvc_RegexRewriteRouter;
  $basicRouter->addRoute('#^(?:.*)/?resource/(.*)$#i', array(
    'controller' => 'resource',
    'action' => 'get',
    'action_params' => array(
      'path' => 1
    )
  ));
  $basicRouter->addRoute('#^(?:.*)/?file/(.*).(pdf|png)$#i', array(
    'controller' => 'file',
    'action' => 'get',
    'action_params' => array(
      'name' => 1,
      'extension' => 2
    )
  ));
  $basicRouter->addRoute('#^apply/([^/]+)/([^/]+)/page/([0-9]+)/?(?:(edit|delete|do)/([0-9]+)/?)?$#i', array(
    'controller' => 'apply_page',
    'action' => 4,
    'action_params' => array(
      'programShortName' => 1,
      'cycleName' => 2,
      'pageID' => 3,
      'answerID' => 5
    )
  ));
  $basicRouter->addRoute('#^apply/([^/]+)/([^/]+)/page/([0-9]+)/do/(.*)/([0-9]+)$#i', array(
    'controller' => 'apply_page',
    'action' => 'do',
    'action_params' => array(
      'programShortName' => 1,
      'cycleName' => 2,
      'pageID' => 3,
      'doWhat' => 4,
      'answerID' => 5
    )
  ));
  $basicRouter->addRoute('#^apply/([^/]+)/([^/]+)/applicant/(login|logout|new|reset)/?$#i', array(
    'controller' => 'apply_applicant',
    'action' => 3,
    'action_params' => array(
      'programShortName' => 1,
      'cycleName' => 2
    )
  ));
  $basicRouter->addRoute('#^apply/([^/]+)/([^/]+)/status/?(.*)$#i', array(
    'controller' => 'apply_status',
    'action' => 3,
    'action_params' => array(
      'programShortName' => 1,
      'cycleName' => 2
    )
  ));
  $basicRouter->addRoute('#^apply/([^/]+)/([^/]+)/support/?(.*)$#i', array(
    'controller' => 'apply_support',
    'action' => 3,
    'action_params' => array(
      'programShortName' => 1,
      'cycleName' => 2
    )
  ));
  $basicRouter->addRoute('#^apply/?([^/]*)/?([^/]*)/?(.*)$#i', array(
    'controller' => 'apply_welcome',
    'action' => '', //do the default
    'action_params' => array(
      'programShortName' => 1,
      'cycleName' => 2
    )
  ));
  $basicRouter->addRoute('#^install/?(.*)/?$#i', array(
    'controller' => 'install',
    'action' => 1
  ));
  $basicRouter->addRoute('#^lor/([^/]+)/?$#i', array(
    'controller' => 'lor',
    'action' => 'index',
    'action_params' => array(
      'urlKey' => 1
    )
  ));
  $fc->addRouter($basicRouter);
  
  
  //Advanced routing needs preg_replace
  $advancedRouter = new Lvc_FullRegexRewriteRouter;
  $advancedRouter->addRoute('#^admin/([^/]+)/?([^/]*)/?(.*)$#i', array(
    'controller' => 'admin_$1',
    'action' => '$2',
    'additional_params' => '$3'
  ));
  $advancedRouter->addRoute('#^manage/([^/]+)/?([^/]*)/?(.*)$#i', array(
    'controller' => 'manage_$1',
    'action' => '$2',
    'additional_params' => '$3'
  ));
  $advancedRouter->addRoute('#^setup/([^/]+)/?([^/]*)/?(.*)$#i', array(
    'controller' => 'setup_$1',
    'action' => '$2',
    'additional_params' => '$3'
  ));
  $advancedRouter->addRoute('#^applicants/([^/]+)/?([^/]*)/?(.*)$#i', array(
    'controller' => 'applicants_$1',
    'action' => '$2',
    'additional_params' => '$3'
  ));
  $fc->addRouter($advancedRouter);
  
  $fc->processRequest(new Lvc_HttpRequest());
} catch (Lvc_Exception $e) {
  //LVC exception result in a 404 Page not Found
  // Log the error message
  trigger_error($e->getMessage());

  // Get a request for the 404 error page.
  $request = new Lvc_Request();
  $request->setControllerName('error');
  $request->setActionName('index');
  $request->setActionParams(array('error' => '404', 'message'=>'Page not found.'));

  // Get a new front controller without any routers, and have it process our handmade request.
  $fc = new Lvc_FrontController();
  $fc->processRequest($request);
  
} catch (Foundation_Exception $e) {
  //Foundation exceptions have a getUserMessage method to display to the user so they get caught first
  trigger_error('Foundation Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine(), E_USER_ERROR);
  // Get a request for the error page
  $request = new Lvc_Request();
  $request->setControllerName('error');
  $request->setActionName('index');
  $request->setActionParams(array('error' => '500', 'message'=>$e->getUserMessage()));

  // Get a new front controller without any routers, and have it process our handmade request.
  $fc = new Lvc_FrontController();
  $fc->processRequest($request);
  
} catch (Exception $e) {
  trigger_error('Uncaught Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine(), E_USER_ERROR);
  // Get a request for the error page
  $request = new Lvc_Request();
  $request->setControllerName('error');
  $request->setActionName('index');
  $request->setActionParams(array('error' => '500', 'message'=>'Unspecified Technical Difficulties'));

  // Get a new front controller without any routers, and have it process our handmade request.
  $fc = new Lvc_FrontController();
  $fc->processRequest($request);
}
?>
