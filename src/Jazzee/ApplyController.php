<?php
namespace Jazzee;
/**
 * Base controller for all authenticated application controllers
 */
class ApplyController extends Controller
{
  /**
   * The application
   * @var \Jazzee\Entity\Application
   */
  protected $_application;
  
  /**
   * Application pages
   * @var array \Jazzee\Entity\ApplicationPage
   */
  protected $_pages;
  
  /**
   * The applicant
   * @var \Jazzee\Entity\Applicant
   */
  protected $_applicant;

  /**
   * Check credentials and intialize members
   */
  public function beforeAction(){
    parent::beforeAction();
    $store = $this->_session->getStore('apply', $this->_config->getApplicantSessionLifetime());
    if(
      !isset($store->applicantID)
    ){
      //Not authenticated 
      $this->addMessage('error', "You are not logged in or your session has expired.  Please log in again");
      $this->redirectPath("apply/{$this->actionParams['programShortName']}/{$this->actionParams['cycleName']}/applicant/login/");
    }
    $this->_applicant = $this->_em->getRepository('\Jazzee\Entity\Applicant')->find($store->applicantID);
    $this->_application = $this->_applicant->getApplication();
    if(!$this->_application->isPublished()){
      $this->addMessage('error', $this->_application->getCycle()->getName() . ' ' . $this->_application->getProgram()->getName() . ' is not open for applicants');
      $this->redirectPath('apply/' . $this->_application->getProgram()->getShortName() .'/');
    }
    foreach($this->_application->getPages() as $pageEntity){
      $pageEntity->getJazzeePage()->setApplicant($this->_applicant);
      $pageEntity->getJazzeePage()->setController($this);
      $this->_pages[$pageEntity->getId()] = $pageEntity;
    }
    $this->setLayoutVar('layoutTitle', $this->_application->getCycle()->getName() . ' ' . $this->_application->getProgram()->getName() . ' Application');
    $this->addCss($this->path('styles/apply.css'));
  }
}