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
    $as = new \SimpleSAML_Auth_Simple('default-sp');
    $as->requireAuth();
    $attrs = $as->getAttributes();
    if (!isset($attrs['eduPersonPrincipalName'][0])) throw new Exception('eduPersonPrincipalName attribute is missing from authentication source.');
    $this->_user = $this->_em->getRepository('\Jazzee\Entity\User')->findOneBy(array('eduPersonPrincipalName'=>$attrs['eduPersonPrincipalName'][0]));
    $this->_user->setFirstName($attrs['givenName'][0]);
    $this->_user->setLastName($attrs['sn'][0]);
    $this->_user->setEmail($attrs['mail'][0]);
    $this->_em->persist($this->_user);
    $store = $this->_session->getStore('admin', $this->_config->getAdminSessionLifetime());
    setcookie('JazzeeAdminLoginTimeout', time()+$this->_config->getAdminSessionLifetime());
    
    if(isset($store->currentProgramId)) $this->_program = $this->_em->getRepository('\Jazzee\Enity\Program')->find($store->currentProgramId);
    if(isset($store->currentCycleId)) $this->_cycle = $this->_em->getRepository('\Jazzee\Enity\Cycle')->find($store->currentCycleId);

    if($this->_cycle AND $this->_program) $this->_application = $this->_em->getRepository('Jazzee\Entity\Application')->findOneByProgramAndCycle($this->program,$this->cycle);

  }
  /**
   * Check set the default page title and layout title
   * don't allow this to be overridden past this point so authentication is always required
   */
  public final function beforeAction(){
    parent::beforeAction();
    if(!$this->checkIsAllowed($this->controllerName, $this->actionName)){
      $this->messages->write('error', "You do not have access to that page.");
      $this->redirect($this->path("admin/welcome"));
      exit();
    }
    if($this->_cycle AND $this->_program){
      $this->setLayoutVar('pageTitle', $this->_cycle->getName() . ' ' . $this->_program->getName());
      $this->setLayoutVar('layoutTitle', $this->_cycle->getName() . ' ' . $this->_program->getName());
    }
    $this->setLayoutVar('navigation', $this->getNavigation());
    $this->setup();
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
   * @return Applicant
   * 
   */
  protected function getApplicantById($applicantId){
    if(!$applicant = $this->application->getApplicantByID($applicantId)){
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to access applicant {$applicantId} who is not in their current program", E_USER_ERROR, 'That applicant does not exist or is not in your current program');
    }
    return $applicant;
  }
}