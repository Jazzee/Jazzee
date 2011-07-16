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
   * Constructor
   * Check for maintenance mode
   */
  public function __construct(){
    parent::__construct();
    if($this->_config->getMode() == 'APPLY_MAINTENANCE'){
      $request = new \Lvc_Request();
      $request->setControllerName('error');
      $request->setActionName('index');
      if(!$message = $this->_config->getMaintenanceModeMessage()) $message = 'The application is currently down for maintenance';
      $request->setActionParams(array('error' => '503', 'message'=>$message));
    
      // Get a new front controller without any routers, and have it process our handmade request.
      $fc = new \Lvc_FrontController();
      $fc->processRequest($request);
      exit();
    }
  }

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
      $this->redirectPath("apply/{$this->actionParams['programShortName']}/{$this->actionParams['cycleName']}/applicant/login");
    }
    $this->_applicant = $this->_em->getRepository('\Jazzee\Entity\Applicant')->find($store->applicantID);
    $this->_application = $this->_applicant->getApplication();
    if(!$this->_application->isPublished()){
      $this->addMessage('error', $this->_application->getCycle()->getName() . ' ' . $this->_application->getProgram()->getName() . ' is not open for applicants');
      $this->redirectPath('apply/' . $this->_application->getProgram()->getShortName() .'/');
    }
    $pages = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findBy(array('application'=>$this->_application->getId(), 'kind'=>\Jazzee\Entity\ApplicationPage::APPLICATION), array('weight'=> 'asc'));
    foreach($pages as $pageEntity){
      $pageEntity->getJazzeePage()->setApplicant($this->_applicant);
      $pageEntity->getJazzeePage()->setController($this);
      $this->_pages[$pageEntity->getId()] = $pageEntity;
    }
    $this->setLayoutVar('layoutTitle', $this->_application->getCycle()->getName() . ' ' . $this->_application->getProgram()->getName() . ' Application');
    $this->setVar('basePath', 'apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName());
    $this->addCss($this->path('resource/styles/apply.css'));
  }
}