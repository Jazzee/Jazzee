<?php
namespace Jazzee;
/**
 * Base controller for all admin controllers
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
abstract class AdminController extends Controller{
  /**
   * The navigation constants for this controller
   * @constant string
   */
  const MENU = null;
  const TITLE = null;
  const PATH = null;
  
  /**
   * AdminAuthentication Class
   * @var \Jazzee\AdminAuthentication
   */
  protected $_adminAuthentication;
  
  /**
   * The user
   * @var \Jazzee\Entity\User
   */
  protected $_user;
  
  /**
   * The current program
   * @var \Jazzee\Entity\Program
   */
  protected $_program;
  
  /**
   * The current Cycle
   * @var \Jazzee\Entity\Cycle
   */
  protected $_cycle;
  
  /**
   * The current application
   * @var \Jazzee\Entity\Application
   */
  protected $_application;
  
  /**
   * Array of direcotires where admin controllers can be found
   * @var array
   */
  protected static $controllerPaths = array();
  
  /**
   * Check saml authentication and store credential into
   */
  public final function __construct(){
    parent::__construct();
    $this->layout = 'wide';
    $class = $this->_config->getAdminAuthenticationClass();
    $this->_adminAuthentication = new $class($this->_em);
    if(!($this->_adminAuthentication instanceof AdminAuthentication)) throw new Exception($this->_config->getAdminAuthenticationClass() . ' does not implement AdminAuthentication Interface.');
    
    if(!$this->_adminAuthentication->isValidUser()){
      //send a 401 not authorized error
      $request = new \Lvc_Request();
      $request->setControllerName('error');
      $request->setActionName('index');
      $request->setActionParams(array('error' => '401', 'message'=>'We were able to log you in successfully, however you do not have permission to access the system.'));
    
      // Get a new front controller without any routers, and have it process our handmade request.
      $fc = new \Lvc_FrontController();
      $fc->processRequest($request);
      exit();
    }
    $this->_user = $this->_adminAuthentication->getUser();
    $store = $this->_session->getStore('admin', $this->_config->getAdminSessionLifetime());
    if($this->_config->getAdminSessionLifetime()){
      setcookie('JazzeeAdminLoginTimeout', time()+$this->_config->getAdminSessionLifetime(), 0, '/');
    } else {
      //if there is no seesion limiter then setup for 24 hours
      setcookie('JazzeeAdminLoginTimeout', time()+86400, 0, '/');
    }
    
    
    if($this->_user->getDefaultCycle()) $this->_cycle = $this->_user->getDefaultCycle();
    if($this->_user->getDefaultProgram()) $this->_program = $this->_user->getDefaultProgram();
    
    if(isset($store->currentProgramId)) $this->_program = $this->_em->getRepository('\Jazzee\Entity\Program')->find($store->currentProgramId);
    if(isset($store->currentCycleId)) $this->_cycle = $this->_em->getRepository('\Jazzee\Entity\Cycle')->find($store->currentCycleId);
    
    if($this->_cycle AND $this->_program) $this->_application = $this->_em->getRepository('Jazzee\Entity\Application')->findOneByProgramAndCycle($this->_program,$this->_cycle);

  }
  /**
   * Check set the default page title and layout title
   * don't allow this to be overridden past this point so authentication is always required
   */
  public final function beforeAction(){
    parent::beforeAction();
    if(!$this->checkIsAllowed($this->controllerName, $this->actionName)){
      $this->addMessage('error', 'You have attempted to access an un-authorized resource.');
      $this->redirect($this->path("admin/welcome"));
      exit();
    }
    if($this->_cycle AND $this->_program){
      $this->setLayoutVar('pageTitle', $this->_cycle->getName() . ' ' . $this->_program->getName());
      $this->setLayoutVar('layoutTitle', $this->_cycle->getName() . ' ' . $this->_program->getName());
    }
    $this->setup();
  }
  
  /**
   * After action
   * 
   * Save the current cycle,progra, and application
   */
  public function afterAction(){
    $store = $this->_session->getStore('admin', $this->_config->getAdminSessionLifetime());
    if($this->_program) $store->currentProgramId = $this->_program->getId();
    if($this->_cycle) $store->currentCycleId = $this->_cycle->getId();
    
    parent::afterAction();
  }
  
  /**
   * Check the credentials or a user
   * At this top level always return false so nothing is allowed by default
   * @param string $controller
   * @param string $action
   * @param \Jazzee\Entity\User $user
   * @param \Jazzee\Entity\Program $program
   * @return bool
   */
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null){
    if($user)  return $user->isAllowed($controller, $action, $program);
    return false;
  }
  
  /**
   * Local shortcut for self::isAllowed
   * @param string $controller
   * @param string $action
   * @return bool
   */
  public function checkIsAllowed($controller, $action = 'index'){
    \Foundation\VC\Config::includeController($controller);
    return call_user_func(array(\Foundation\VC\Config::getControllerClassName($controller), 'isAllowed'),$controller, $action, $this->_user, $this->_program);
  }
  
  /**
   * Perform any setup
   * Since beforeAction is final this allows controllers to do some setup of their own
   */
  protected function setUp(){}
  
  /**
   * Get Navigation
   */
  public function getNavigation(){
    if($this->_cache->contains('AdminControllerGetNavigation')) return $this->_cache->fetch('AdminControllerGetNavigation');
    $navigation = new \Foundation\Navigation\Container();
    $menus = array();
    foreach($this->listControllers() as $controller){
      if($this->checkIsAllowed($controller)){
        \Foundation\VC\Config::includeController($controller);
        $class = \Foundation\VC\Config::getControllerClassName($controller);
        if(!is_null($class::MENU)){
          if(!isset($menus[$class::MENU])){
            $menus[$class::MENU] = new \Foundation\Navigation\Menu();
            $menus[$class::MENU]->setTitle($class::MENU);
            $navigation->addMenu($menus[$class::MENU]);
          }
          $link = new \Foundation\Navigation\Link($class::TITLE);
          $link->setHref($this->path($class::PATH));
          $menus[$class::MENU]->addLink($link);
        }
      }
    }
    foreach($menus as $menu) $menu->sortLinks();
    if(empty($menus)) return false;  //if there are no controllers or no authorization there are no menus
    $this->_cache->save('AdminControllerGetNavigation', $navigation);
    return $navigation;
  }
  
  /**
   * Add a path to the AdminController::controllersPaths
   * @param string $path
   * @throws Jazzee_Exception
   */
  public static function addControllerPath($path){
    if(!is_readable($path)) throw new Exception("Unable to read controller path {$path}");
    self::$controllerPaths[] = $path;
  }
  
  /**
   * List all the controllers
   * @return array
   */
  protected function listControllers(){
    $arr = array();
    foreach(self::$controllerPaths as $path){
      //scan the directory but drop the relative paths
      foreach(array_diff(scandir($path), array('.','..')) as $fileName) 
        $arr[] = basename($fileName, '.php');
    }
    return $arr;
  }
  
  /**
   * Run the cron task for this controller
   * @param integer $lastRun the last time this task was successfully completed in seconds
   * @return bool true if successfull false otherwise
   */
  public static function runCron($lastRun){
    return false;
  }
  
  /**
   * Notify and applicant that their status has changed
   * Several controllers (ApplicantsView, ApplicantsDecisions) can change an applicants status (decisions, sir, answer status)
   * Use this method to send them notifications 
   * @param Applicant $applicant
   */
  protected function notifyApplicantStatusUpdate(Applicant $applicant){
    $mail = JazzeeMail::getInstance();
    $message = new EmailMessage;
    $message->to($applicant->email, "{$applicant->firstName} {$applicant->lastName}");
    $message->from($applicant->Application->contactEmail, $applicant->Application->contactName);
    $message->subject = 'Application Status';
    $message->body = "We have updated your application status.  In order to protect your privacy you must login to see these changes.  " . $mail->path("apply/{$applicant->Application->Program->shortName}/{$applicant->Application->Cycle->name}/applicant/login");
    $mail->send($message);
  }
  
  /**
   * Get an applicant by ID
   * Ensures we are fetching an applicant from our current program and cycle
   * @param integer $applicantId
   * @return \Jazzee\Entity\Applicant
   * 
   */
  protected function getApplicantById($applicantId){
    if(!$applicant = $this->_em->getRepository('\Jazzee\Entity\Applicant')->findOneBy(array('id'=>$applicantId, 'application'=>$this->_application->getId()))){
      throw new Exception($this->_user->getFirstName() . ' ' . $this->_user->getLastName() . ' (#' . $this->_user->getId() . ") attempted to access applicant {$applicantId} who is not in their current program", E_USER_ERROR, 'That applicant does not exist or is not in your current program');
    }
    return $applicant;
  }
}