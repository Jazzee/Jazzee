<?php
/**
 * Loads Files stores in the session
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class FileController extends \Jazzee\Controller{
  /**
   * Output a single file
   * @param string $name 
   */
  public function actionGet($name){
    if($file = $this->getStoredFile($name)) $file->output();
    //send a 404
    $request = new Lvc_Request();
    $request->setControllerName('error');
    $request->setActionName('index');
    $request->setActionParams(array('error' => '404', 'message'=>'File Not Found'));
  
    // Get a new front controller without any routers, and have it process our handmade request.
    $fc = new Lvc_FrontController();
    $fc->processRequest($request);
    exit();
  }
}
?>