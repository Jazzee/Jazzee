<?php
/**
 * Base controller for all admin controllers
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
abstract class AdminController extends JazzeeController{
  /**
   * The navigation constants for this controller
   * @constant string
   */
  const MENU = null;
  const TITLE = null;
  const PATH = null;
  
  /**
   * The user
   * @var User
   */
  protected $user;
  
  /**
   * The current program
   * @var Program
   */
  protected $program;
  
  /**
   * The current Cycle
   * @var Cycle
   */
  protected $cycle;
  
  /**
   * The current application
   * @var Application
   */
  protected $application;

  /**
   * Set the layout to wide
   * @var string
   */
  protected $layout = 'wide';
  
  /**
   * Array of direcotires where admin controllers can be found
   * @var array
   */
  protected static $controllerPaths = array();
  
  /**
   * Check credentials and intialize members
   */
  public final function beforeAction(){
    parent::beforeAction();
    $this->session = Session::getInstance()->getStore('admin', $this->config->session_lifetime);
    setcookie('JazzeeAdminLoginTimeout', time()+$this->config->session_lifetime);
    if(isset($this->session->userID))
      $this->user = Doctrine::getTable('User')->find($this->session->userID);
    if(isset($this->session->programID))
      $this->program = Doctrine::getTable('Program')->find($this->session->programID);
    if(isset($this->session->cycleID))
      $this->cycle = Doctrine::getTable('Cycle')->find($this->session->cycleID);
    if($this->cycle AND $this->program){
       $this->application = Doctrine::getTable('Application')->findOneByProgramIDAndCycleID($this->program->id, $this->cycle->id);
    }
    if(!$this->checkIsAllowed($this->controllerName, $this->actionName)){
      if($this->controllerName == 'admin_welcome' and $this->actionName == 'index'){
        //Not authenticated 
        $session = Session::getInstance();
        $this->session = $session->getStore('guest');
        $this->messages->write('error', "You are not logged in or your session has expired.  Please log in again.");
        $this->redirect($this->path("admin/login"));
        $this->afterAction();
        exit(); 
      }
      $this->messages->write('error', "You do not have access to that page.");
      $this->redirect($this->path("admin/welcome"));
      $this->afterAction();
      exit();
    }
    if($this->cycle AND $this->program){
      $this->setLayoutVar('pageTitle', $this->cycle->name . ' ' . $this->program->name);
      $this->setLayoutVar('layoutTitle', $this->cycle->name . ' ' . $this->program->name);
    }
    $this->setUp();
  }
  
  /**
   * Check the credentials or a user
   * At this top level always return false so nothing is allowed by default
   * @param string $controller
   * @param string $action
   * @param User $user
   * @param Program $program
   * @param Cycle $cycle
   * @param array $actionParams
   * @return false
   */
  public static function isAllowed($controller, $action, $user, $programID, $cycleID, $actionParams){
    if($user)  return $user->isAllowed($controller, $action, $programID);
    return false;
  }
  
  /**
   * Local shortcut for self::isAllowed
   * @param string $controller
   * @param string $action
   * @return bool
   */
  public function checkIsAllowed($controller, $action = 'index'){
    FoundationVC_Config::includeController($controller);
    $programID = $this->program?$this->program->id:null;
    $cycleID = $this->cycle?$this->cycle->id:null;
    return call_user_func(array(Lvc_Config::getControllerClassName($controller), 'isAllowed'),$controller, $action, $this->user, $programID, $cycleID, $this->actionParams);
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
    $navigation = new Navigation();
    $menus = array();
    foreach($this->listControllers() as $controller){
      if($this->checkIsAllowed($controller)){
        FoundationVC_Config::includeController($controller);
        $class = Lvc_Config::getControllerClassName($controller);
        if(!is_null($class::MENU)){
          if(!isset($menus[$class::MENU])){
            $menus[$class::MENU] = $navigation->newMenu();
            $menus[$class::MENU]->title = $class::MENU;
          }
          $menus[$class::MENU]->newLink(array('text'=>$class::TITLE, 'href'=>$this->path($class::PATH).'/'));
        }
      }
    }
    foreach($menus as $menu) $menu->sortLinks();
    return $navigation;
  }
  
  /**
   * Add a path to the AdminController::controllersPaths
   * @param string $path
   * @throws Jazzee_Exception
   */
  public static function addControllerPath($path){
    if(!is_readable($path)) throw new Jazzee_Exception("Unable to read controller path {$path}");
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
   * Get a list of all of the AdminAuthControllers
   */
  protected function getAuthObjects(){
    
  }
}
?>