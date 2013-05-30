<?php

/**
 * Preview Application
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class PreviewController extends \Jazzee\Controller
{

  /**
   * Put a user into preview mode
   * @param string $key
   * @throws \Jazzee\Exception
   */
  public function actionStart($key)
  {
    $path = $this->_config->getVarPath() . '/tmp/' . $key . '.previewdb.db';
    if(!is_writable($path)){
      throw new \Jazzee\Exception("Preview database at {$path} does not exist.", E_USER_ERROR, 'This application preview no longer exists.');
    }

    $doctrineConfig = $this->_em->getConfiguration();
    $connectionParams = array(
      'driver' => 'pdo_sqlite',
      'path' => $path
    );
    $em = \Doctrine\ORM\EntityManager::create($connectionParams, $doctrineConfig);

    $program = $em->getRepository('\Jazzee\Entity\Program')->findOneBy(array());
    $cycle = $em->getRepository('\Jazzee\Entity\Cycle')->findOneBy(array());

    $store = $this->_session->getStore('preview', 3600);
    $store->set('previewdbpath', $path);

    $this->redirectPath('apply/' . $program->getShortName() . '/' . $cycle->getName());
  }

  public function actionEnd(){
    $store = $this->_session->getStore('preview', 3600);
    $store->remove('previewdbpath');
    //clear all the messages
    $this->getMessages();
    $this->addMessage('success', 'Your preview session has ended.');
    $store = $this->_session->getStore(\Jazzee\AdminController::SESSION_STORE_NAME, 1);
    $store->expire();
    $this->redirectPath('admin/login');
  }

}