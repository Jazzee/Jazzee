<?php

namespace Jazzee;

/**
 * Base controller for all admin controllers
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AdminController extends Controller implements \Jazzee\Interfaces\AdminController
{
  /**
   * The navigation constants for this controller
   * @constant string
   */

  const MENU = null;
  const TITLE = null;
  const PATH = null;

  /**
   * Is authorization required or can any user acess
   * @constant boolean
   */
  const REQUIRE_AUTHORIZATION = true;

  /**
   * Can this resource be accessed if not application is selected
   * @constant boolean
   */
  const REQUIRE_APPLICATION = true;
  
  /**
   * The name of the admin session store
   * @var string
   */
  const SESSION_STORE_NAME = 'admin';

  /**
   * AdminAuthentication Class
   * @var \Jazzee\AdminAuthentication
   */
  protected $_adminAuthentication;

  /**
   * AdminDirectory Class
   * @var \Jazzee\AdminDirectory
   */
  protected $_adminDirectory;

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
   * Session Store
   * @var \Foundation\Session\Store
   */
  protected $_store;

  /**
   * Array of direcotires where admin controllers can be found
   * @var array
   */
  protected static $controllerPaths = array();

  /**
   * Check saml authentication and store credential into
   */
  public final function __construct()
  {
    parent::__construct();
    $this->layout = 'wide';
    $this->_store = $this->_session->getStore(self::SESSION_STORE_NAME, $this->_config->getAdminSessionLifetime());
    if($this->isPreviewMode()){
      $class = '\Jazzee\AdminAuthentication\PreviewApplication';
    } else {
      $class = $this->_config->getAdminAuthenticationClass();
    }
    $this->_adminAuthentication = new $class($this);
    if (!($this->_adminAuthentication instanceof Interfaces\AdminAuthentication)) {
      throw new Exception($this->_config->getAdminAuthenticationClass() . ' does not implement AdminAuthentication Interface.');
    }
    if ($this->_adminAuthentication->isValidUser()) {
      $this->_user = $this->_adminAuthentication->getUser();

      if ($this->_user->getDefaultProgram()) {
        $this->_program = $this->_user->getDefaultProgram();
      } else {
        if ($programs = $this->_user->getPrograms()) {
          $programId = array_pop($programs);
          $program = $this->_em->getRepository('\Jazzee\Entity\Program')->find($programId);
          $this->_program = $program;
          $this->_user->setDefaultProgram($program);
          $this->_em->persist($this->_user);
        }
      }
      if ($this->_user->getDefaultCycle()) {
        $this->_cycle = $this->_user->getDefaultCycle();
      } else {
        if ($cycle = $this->_em->getRepository('\Jazzee\Entity\Cycle')->findBestCycle($this->_program)) {
          $this->_cycle = $cycle;
          $this->_user->setDefaultCycle($cycle);
          $this->_em->persist($this->_user);
        }
      }

      if (isset($this->_store->currentProgramId)) {
        $this->_program = $this->_em->getRepository('\Jazzee\Entity\Program')->find($this->_store->currentProgramId);
      }
      if (isset($this->_store->currentCycleId)) {
        $this->_cycle = $this->_em->getRepository('\Jazzee\Entity\Cycle')->find($this->_store->currentCycleId);
      }

      if ($this->_cycle AND $this->_program) {
        if (!$this->_application = $this->_em->getRepository('Jazzee\Entity\Application')->findOneByProgramAndCycle($this->_program, $this->_cycle)) {
          $this->_application = null;
        }
      }
    } else {
      //expire the store for non users - so there are no navigation or caching problems
      $this->_store->expire();
    }

    if ($this->_config->getAdminSessionLifetime()) {
      setcookie('JazzeeAdminLoginTimeout', time() + $this->_config->getAdminSessionLifetime(), 0, '/');
    } else {
      //if there is no seesion limiter then setup for 24 hours
      setcookie('JazzeeAdminLoginTimeout', time() + 86400, 0, '/');
    }
  }

  /**
   * Check set the default page title and layout title
   * don't allow this to be overridden past this point so authentication is always required
   * @SuppressWarnings(PHPMD.ExitExpression)
   */
  public final function beforeAction()
  {
    parent::beforeAction();
    if (!$this->checkIsAllowed($this->controllerName, $this->actionName)) {
      $this->addMessage('error', 'You have attempted to access an un-authorized resource.');
      $this->redirect($this->path('welcome'));
      exit();
    }
    if ($this->_cycle AND $this->_program) {
      if (!$this->checkIsAllowed('admin_changecycle')) {
        $this->_cycle = $this->_em->getRepository('\Jazzee\Entity\Cycle')->findBestCycle($this->_program);
      }
      $this->setLayoutVar('pageTitle', $this->_cycle->getName() . ' ' . $this->_program->getName());
      $this->setLayoutVar('layoutTitle', $this->_cycle->getName() . ' ' . $this->_program->getName());
    }
    $this->addScript($this->path('resource/scripts/classes/Status.class.js'));
    $this->addScript($this->path('resource/scripts/classes/AuthenticationTimeout.class.js'));
    $this->addScript($this->path('resource/scripts/controllers/admin.controller.js'));
    $this->setup();
  }

  /**
   * After action
   *
   * Save the current cycle,progra, and application
   */
  public function afterAction()
  {
    if ($this->_program) {
      $this->_store->currentProgramId = $this->_program->getId();
    }
    if ($this->_cycle) {
      $this->_store->currentCycleId = $this->_cycle->getId();
    }

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
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null)
  {
    $class = \Foundation\VC\Config::getControllerClassName($controller);
    if (!$class::REQUIRE_AUTHORIZATION) {
      return true;
    }
    if ($class::REQUIRE_APPLICATION and is_null($application)) {
      return false;
    }
    if ($user) {
      return $user->isAllowed($controller, $action, $program);
    }

    return false;
  }

  /**
   * Local shortcut for self::isAllowed
   * @param string $controller
   * @param string $action
   * @return bool
   */
  public function checkIsAllowed($controller, $action = 'index')
  {
    \Foundation\VC\Config::includeController($controller);

    return call_user_func(array(\Foundation\VC\Config::getControllerClassName($controller), 'isAllowed'), $controller, $action, $this->_user, $this->_program, $this->_application);
  }

  /**
   * Perform any setup
   * Since beforeAction is final this allows controllers to do some setup of their own
   */
  protected function setUp()
  {

  }

  /**
   * Get Navigation
   */
  public function getNavigation()
  {
    if (isset($this->_store->AdminControllerGetNavigation)) {
      return $this->_store->AdminControllerGetNavigation;
    }
    $navigation = new \Foundation\Navigation\Container();
    $link = new \Foundation\Navigation\Link('Home');
    $link->setHref($this->path('welcome'));
    $navigation->addLink($link);
    $menus = array();
    foreach ($this->listControllers() as $controller) {
      if ($this->checkIsAllowed($controller)) {
        \Foundation\VC\Config::includeController($controller);
        $class = \Foundation\VC\Config::getControllerClassName($controller);
        if (!is_null($class::MENU)) {
          if (!isset($menus[$class::MENU])) {
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
    foreach ($menus as $menu) {
      $menu->sortLinks();
    }
    if (empty($menus)) {
      return false;  //if there are no controllers or no authorization there are no menus
    }
    $this->_store->AdminControllerGetNavigation = $navigation;

    return $navigation;
  }

  /**
   * Add a path to the AdminController::controllersPaths
   * @param string $path
   * @throws Jazzee_Exception
   */
  public static function addControllerPath($path)
  {
    if (!is_readable($path)) {
      throw new Exception("Unable to read controller path {$path}");
    }
    self::$controllerPaths[] = $path;
  }

  /**
   * List all the controllers
   * @return array
   */
  protected function listControllers()
  {
    $arr = array();
    foreach (self::$controllerPaths as $path) {
      //scan the directory but drop the relative paths
      foreach (array_diff(scandir($path), array('.', '..')) as $fileName) {
        $arr[] = basename($fileName, '.php');
      }
    }

    return $arr;
  }

  /**
   * Get an applicant by ID
   * Ensures we are fetching an applicant from our current program and cycle
   * @param integer $applicantId
   * @return \Jazzee\Entity\Applicant
   *
   */
  protected function getApplicantById($applicantId)
  {
    if (!$applicant = $this->_em->getRepository('\Jazzee\Entity\Applicant')->find($applicantId, false) or $applicant->getApplication() != $this->_application) {
      throw new Exception($this->_user->getFirstName() . ' ' . $this->_user->getLastName() . ' (#' . $this->_user->getId() . ") attempted to access applicant {$applicantId} who is not in their current program", E_USER_ERROR, 'That applicant does not exist or is not in your current program');
    }

    return $applicant;
  }

  /**
   * Prepend admin/ to all the paths
   * @param string $path
   */
  public function path($path)
  {
    return parent::path('admin/' . $path);
  }

  /**
   * Prepend admin/ to all the paths
   * @param string $path
   */
  public function absolutePath($path) {
    return parent::absolutePath('admin/' . $path);
  }

  /**
   * Create a path to the apply side
   * @param string $path
   * @return string
   */
  public function applyPath($path)
  {
    return parent::path($path);
  }

  /**
   * Create a path to the apply side
   * @param string $path
   * @return string
   */
  public function absoluteApplyPath($path)
  {
    return parent::absolutePath($path);
  }

  /**
   * Get the session store
   * @return \Foundation\Session\Store
   */
  public function getStore()
  {
    return $this->_store;
  }

  /**
   * Get the admim directory create it if necessary
   *
   * @return \Jazzee\AdminDirectory
   */
  public function getAdminDirectory()
  {
    if (!$this->_adminDirectory) {
      $class = $this->_config->getAdminDirectoryClass();
      $this->_adminDirectory = new $class($this);
      if (!($this->_adminDirectory instanceof Interfaces\AdminDirectory)) {
        throw new Exception($this->_config->getAdminDirectoryClass() . ' does not implement AdminDirectory Interface.');
      }
    }

    return $this->_adminDirectory;
  }
  
  /**
   * List the displays for a user
   * 
   * @return array
   */
  protected function listDisplays(){
    $userMaximumDisplay = $this->_user->getMaximumDisplayForApplication($this->_application);
    $displays = array();
    foreach($this->_em->getRepository('Jazzee\Entity\Display')->findBy(array('type'=>'user','user'=>$this->_user, 'application'=>$this->_application)) as $userDisplay){
      $intersection = new \Jazzee\Display\Intersection();
      $intersection->addDisplay($userDisplay);
      $intersection->addDisplay($userMaximumDisplay);
      $displays[] = array(
        'type' => 'user',
        'id'  => $userDisplay->getId(),
        'name' => $userDisplay->getName(),
        'pageIds' => $intersection->getPageIds(),
        'elementIds' => $intersection->getElementIds(),
        'elements' => $intersection->listElements()
      );
    }
    $systemDisplays = array('\Jazzee\Display\Minimal');
    foreach($systemDisplays as $class){
      $display = new $class($this->_application);
      $intersection = new \Jazzee\Display\Intersection();
      $intersection->addDisplay($display);
      $intersection->addDisplay($userMaximumDisplay);
      $displays[] = array(
        'id' => $display->getId(),
        'type' => 'system',
        'class'  => get_class($display),
        'name'  =>  $display->getName(),
        'pageIds' => $intersection->getPageIds(),
        'elementIds' => $intersection->getElementIds(),
        'elements' => $intersection->listElements()
      );
    }
    
    return $displays;
  }
  
  protected function getDisplay(array $arr){
    $intersection = new \Jazzee\Display\Intersection();
    $intersection->addDisplay($this->_user->getMaximumDisplayForApplication($this->_application));
    switch($arr['type']){
      case 'user':
        $display = $this->_em->getRepository('Jazzee\Entity\Display')->findOneBy(array('id'=>$arr['id'], 'user'=>$this->_user));
        $intersection->addDisplay($display);
        return $intersection;
        break;
      case 'system':
        $display = new $arr['class']($this->_application);
        $intersection->addDisplay($display);
        return $intersection;
        break;
      default:
        throw new Exception('Unkown display type ' . $arr['type']);
    }
  }

}
