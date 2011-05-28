<?php
/**
 * Routing for requests
 * 
 * All the posible routes get mapped here and matched in the front controller
 */
try {
  $fc = new \Foundation\VC\FrontController();
  
  $basicRouter = new Lvc_RegexRewriteRouter;
  
  //anything with a trailing slash gets redirected without it
  //this should be done with modrewrite so we get a permanent redirect, but it is here in case
  //modrewrite isn't available
  $basicRouter->addRoute('#(.*)/$#', array( 
    'redirect' => 'index.php?url=$1'
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
  $basicRouter->addRoute('#^apply/([^/]+)/([^/]+)/page/([0-9]+)/?(?:(index|edit|delete|do)/([0-9]+)(/[0-9]+)?)?$#i', array(
    'controller' => 'apply_page',
    'action' => 4,
    'action_params' => array(
      'programShortName' => 1,
      'cycleName' => 2,
      'pageID' => 3,
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
  $fc->addRouter($basicRouter);
  
  
  //We use preg replace in the admin routers to cleanly group the administrative responsiblities in the URL
  $advancedRouter = new \Foundation\VC\FullRegexRewriteRouter();
  $advancedRouter->addRoute('#^(admin|maange|setup|applicant)/([^/]+)/?([^/]*)/?(.*)$#i', array(
    'controller' => '$1_$2',
    'action' => '$3',
    'additional_params' => '$4'
  ));
  
  //applicant actions
  $advancedRouter->addRoute('#^apply/([^/]+)/([^/]+)/(applicant|status|support)/(.*)$#i', array(
    'controller' => 'apply_$3',
    'action' => '$4',
    'action_params' => array(
      'programShortName' => '$1',
      'cycleName' => '$2'
    )
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
  
} catch (\Foundation\Virtual\Exception $e) {
  //Virtual Exceptions set a html header error code
  // Get a request for the error page
  $request = new Lvc_Request();
  $request->setControllerName('error');
  $request->setActionName('index');
  $request->setActionParams(array('error' => $e->getHttpErrorCode(), 'message'=>$e->getUserMessage()));

  // Get a new front controller without any routers, and have it process our handmade request.
  $fc = new Lvc_FrontController();
  $fc->processRequest($request);

} catch (PDOException $e){
  throw new \Jazzee\Exception("Problem with database connection. PDO says: " . $e->getMessage(), E_ERROR, 'We are experiencing a problem connecting to our database.  Please try your request again.');
  
} catch (\Foundation\Exception $e) {
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