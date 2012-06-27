<?php

/**
 * Loads Virtual Files
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class VirtualfileController extends \Jazzee\Controller
{

  /**
   * Output a single file
   * @param string $name
   */
  public function actionGet($name)
  {
    if ($file = $this->_em->getRepository('\Jazzee\Entity\VirtualFile')->findOneBy(array('name' => $name))) {
      $virtualFile = new \Foundation\Virtual\VirtualFile($file->getName(), $file->getContents());
      $virtualFile->output();
    }

    //send a 404
    $request = new Lvc_Request();
    $request->setControllerName('error');
    $request->setActionName('index');
    $request->setActionParams(array('error' => '404', 'message' => 'File Not Found'));

    // Get a new front controller without any routers, and have it process our handmade request.
    $frontController = new Lvc_FrontController();
    $frontController->processRequest($request);
  }

}