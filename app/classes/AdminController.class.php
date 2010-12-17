<?php
/**
 * Base controller for all admin controllers
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
abstract class AdminController extends JazzeeController{
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
   * Check credentials and intialize members
   */
  public final function beforeAction(){
    parent::beforeAction();
    $this->session = Session::getInstance()->getStore('admin', $this->config->session_lifetime);
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
    Lvc_FoundationConfig::includeController($controller);
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
    $menu = $navigation->newMenu();
    $menu->title = 'Manage System';
    if($this->checkIsAllowed('manage_configuration'))
      $menu->newLink(array('text'=>'Configuration', 'href'=>$this->path("manage/configuration/")));
    if($this->checkIsAllowed('manage_cycles'))
      $menu->newLink(array('text'=>'Application Cycles', 'href'=>$this->path("manage/cycles/")));
    if($this->checkIsAllowed('manage_programs'))
      $menu->newLink(array('text'=>'Programs', 'href'=>$this->path("manage/programs/")));
    if($this->checkIsAllowed('manage_users'))
      $menu->newLink(array('text'=>'Users', 'href'=>$this->path("manage/users/")));
    if($this->checkIsAllowed('manage_roles'))
      $menu->newLink(array('text'=>'Global Roles', 'href'=>$this->path("manage/roles/")));
    if($this->checkIsAllowed('manage_globalpages'))
      $menu->newLink(array('text'=>'Global Pages', 'href'=>$this->path("manage/globalpages/")));
    if($this->checkIsAllowed('manage_pagetypes'))
      $menu->newLink(array('text'=>'Page Types', 'href'=>$this->path("manage/pagetypes/")));
    if($this->checkIsAllowed('manage_elementtypes'))
      $menu->newLink(array('text'=>'Element Types', 'href'=>$this->path("manage/elementtypes/")));
    if($this->checkIsAllowed('manage_scores'))
      $menu->newLink(array('text'=>'Test Scores', 'href'=>$this->path("manage/scores/")));
      
    $menu = $navigation->newMenu();
    $menu->title = 'Setup';
    if($this->checkIsAllowed('setup_application'))
      $menu->newLink(array('text'=>'Application', 'href'=>$this->path("setup/application/")));
    if($this->checkIsAllowed('setup_pages'))
      $menu->newLink(array('text'=>'Pages', 'href'=>$this->path("setup/pages/")));
    if($this->checkIsAllowed('setup_roles'))
      $menu->newLink(array('text'=>'Program Roles', 'href'=>$this->path("setup/roles/")));
    if($this->checkIsAllowed('setup_users'))
      $menu->newLink(array('text'=>'Program Users', 'href'=>$this->path("setup/users/")));
    
    $menu = $navigation->newMenu();
    $menu->title = 'Applicants';
    if($this->checkIsAllowed('applicants_view'))
      $menu->newLink(array('text'=>'View Applicants', 'href'=>$this->path("applicants/view/")));
    
    
    $menu = $navigation->newMenu();
    $menu->title = 'My Account';
    if($this->checkIsAllowed('admin_changecycle'))
      $menu->newLink(array('text'=>'Change Cycle', 'href'=>$this->path("admin/changecycle/")));
    if($this->checkIsAllowed('admin_changeprogram'))
      $menu->newLink(array('text'=>'Change Program', 'href'=>$this->path("admin/changeprogram/")));
    if($this->checkIsAllowed('admin_profile'))
      $menu->newLink(array('text'=>'Profile', 'href'=>$this->path("admin/profile/")));
    $menu->newLink(array('text'=>'Logout', 'href'=>$this->path("admin/logout")));
    return $navigation;
  }
  
  /**
   * Get a variable
   * @param string $name
   * @return blob the value
   */
  protected function getVariable($name){
    $var = Doctrine::getTable('AdminVariable')->findOneByName($name);
    if($var){
      return $var->value;
    }
    return false;
  }
  
  /**
   * Set a variable
   * @param string $name
   * @param mixed $value
   */
  protected function setVariable($name, $value){
    $var = Doctrine::getTable('AdminVariable')->findOneByName($name);
    if($var === false){
      $var = new AdminVariable;
      $var->name = $name; 
    }
    $var->value = (string)$value;
    $var->save();
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