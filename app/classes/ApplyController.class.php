<?php
/**
 * Base controller for all authenticated application controllers
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage apply
 */
class ApplyController extends JazzeeController{
  /**
   * The application
   * @var Application
   */
  protected $application;
  
  /**
   * All of the application pages
   */
  protected $pages;
  
  /**
   * The applicant
   * @var Applicant
   */
  protected $applicant;

  /**
   * Check credentials and intialize members
   */
  public function beforeAction(){
    parent::beforeAction();
    $this->session = Session::getInstance()->getStore('apply', $this->config->session_lifetime);
    if(
      !isset($this->session->applicantID)
    ){
      //Not authenticated 
      $this->messages->write('error', "You are not logged in or your session has expired.  Please log in again");
      $this->redirect($this->path("apply/{$this->actionParams['programShortName']}/{$this->actionParams['cycleName']}/applicant/login/"));
      $this->afterAction();
      exit();
    }
    $this->applicant = Doctrine::getTable('Applicant')->find($this->session->applicantID);
    $this->application = $this->applicant->Application;
    foreach($this->application->Pages as $page){
      if(class_exists($page->Page->PageType->class) AND is_subclass_of($page->Page->PageType->class, 'ApplyPage')){
        $this->pages[$page->id] = new $page->Page->PageType->class($page, $this->applicant);
      } else {
        throw new Jazzee_Exception("There is no {$page->Page->PageType->class} class available.  This page will not be displayed");
      }
    }
    $this->setLayoutVar('layoutTitle', $this->application->Cycle->name . ' ' . $this->application->Program->name);
    $this->addCss('common/styles/apply.css');
  }
}
?>