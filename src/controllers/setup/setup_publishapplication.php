<?php
/**
 * Publish the application
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 */
class SetupPublishapplicationController extends \Jazzee\AdminController {
  const MENU = 'Setup';
  const TITLE = 'Publish Application';
  const PATH = 'setup/publishapplication';
  
  const ACTION_INDEX = 'Check if the application is ready to be published';
  const ACTION_PUBLISH = 'Publish application';
  const ACTION_UNPUBLISH = 'Un-Publish application';
  const ACTION_PUBLISHOVERRIDE = 'Publish application that is not ready';
  
  
  /**
   * Display publication status and check if the app can be published
   */
  public function actionIndex(){
    $ready = true;
    if(!$this->_application->canPublish()){
      $problems = array();
      foreach($this->_cycle->getRequiredPages() as $requiredPage){
        if(!$this->_application->hasPage($requiredPage)){
          $ready = false;
          $problems[] = "{$requiredPage->getTitle()} page is required, but is not in the application.";
        }
      }
      $this->setVar('problems', $problems);
    }
    $this->setVar('published', $this->_application->isPublished());
    $this->setVar('ready', $ready);
  }
  
  /**
   * Publish the application
   */
  public function actionPublish(){
    $this->_application->publish(false);
    $this->_em->persist($this->_application);
    $this->addMessage('success', 'Application Published.');
    $this->redirectPath('setup/publishapplication');
  }
  
  /**
   * Un Publish an application 
   */
  public function actionUnpublish(){
    $this->_application->unPublish();
    $this->_em->persist($this->_application);
    $this->addMessage('success', 'Application Un-Published.');
    $this->redirectPath('setup/publishapplication');
  }
  
  /**
   * Publish the application that is not ready
   */
  public function actionPublishoverride(){
    $this->_application->publish(true);
    $this->_em->persist($this->_application);
    $this->addMessage('success', 'Application Published.');
    $this->redirectPath('setup/publishapplication');
  }
  
}