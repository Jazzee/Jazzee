<?php

/**
 * Welcome and information for un-authenticated applicants
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplyWelcomeController extends \Jazzee\Controller
{

  /**
   * The program if it is available
   * @var Program
   */
  protected $program;

  /**
   * The Cycle if it is available
   * @var Cycle
   */
  protected $cycle;

  /**
   * The application if it is available
   * @var Application
   */
  protected $application;

  /**
   * Constructor
   * Check for maintenance mode
   * @SuppressWarnings(PHPMD.ExitExpression)
   */
  public function __construct()
  {
    parent::__construct();
    if ($this->_config->getMode() == 'APPLY_MAINTENANCE') {
      $request = new \Lvc_Request();
      $request->setControllerName('error');
      $request->setActionName('index');
      if (!$message = $this->_config->getMaintenanceModeMessage()) {
        $message = 'The application is currently down for maintenance';
      }
      $request->setActionParams(array('error' => '503', 'message' => $message));

      // Get a new front controller without any routers, and have it process our handmade request.
      $frontController = new \Lvc_FrontController();
      $frontController->processRequest($request);
      exit(0);
    }
  }

  /**
   * Before any action do some setup
   * If we know the program and cycle load the applicant var
   * If we only know the program fill that in
   * @return null
   */
  protected function beforeAction()
  {
    parent::beforeAction();
    if (!empty($this->actionParams['programShortName'])) {
      $this->program = $this->_em->getRepository('Jazzee\Entity\Program')->findOneByShortName($this->actionParams['programShortName']);
    }
    if (!empty($this->actionParams['cycleName'])) {
      $this->cycle = $this->_em->getRepository('Jazzee\Entity\Cycle')->findOneByName($this->actionParams['cycleName']);
    }
    if (!is_null($this->program) AND !is_null($this->cycle)) {
      $this->application = $this->_em->getRepository('Jazzee\Entity\Application')->findOneByProgramAndCycle($this->program, $this->cycle);
    }
  }

  /**
   * Display welcome information
   * Might display list of program, cycles in a program, or an application welcome page dependign on the url
   * Enter description here ...
   */
  public function actionIndex()
  {
    if (is_null($this->program)) {
      $this->setVar('programs', $this->_em->getRepository('Jazzee\Entity\Program')->findAllActive());
      $this->setLayoutVar('layoutTitle', 'Select a Program');
      $this->loadView($this->controllerName . '/programs');

      return true;
    }
    if (empty($this->application)) {
      $this->setLayoutVar('layoutTitle', $this->program->getName() . ' Application');
      $this->setVar('applications', $this->_em->getRepository('Jazzee\Entity\Application')->findByProgram($this->program, false, true));
      $this->loadView($this->controllerName . '/cycles');

      return true;
    }
    if (!$this->application->isPublished() or $this->application->getOpen() > new DateTime('now')) {
      $this->addMessage('error', $this->application->getCycle()->getName() . ' ' . $this->application->getProgram()->getName() . ' is not open for applicants');
      $this->redirectPath('apply/' . $this->application->getProgram()->getShortName());
    }
    $this->setLayoutVar('layoutTitle', $this->cycle->getName() . ' ' . $this->program->getName() . ' Application');
    $this->setVar('application', $this->application);
  }

  /**
   * Get the navigation
   * @return Navigation
   */
  public function getNavigation()
  {
    if (empty($this->program) AND empty($this->application)) {
      return null;
    }
    $navigation = new \Foundation\Navigation\Container();
    $menu = new \Foundation\Navigation\Menu();

    $menu->setTitle('Navigation');
    if (empty($this->application)) {
      $link = new \Foundation\Navigation\Link('Welcome');
      $link->setHref($this->path('apply'));
      $menu->addLink($link);
    } else {
      $path = 'apply/' . $this->program->getShortName() . '/' . $this->cycle->getName();
      $link = new \Foundation\Navigation\Link('Welcome');
      $link->setHref($this->path($path));
      $link->setCurrent(true);
      $menu->addLink($link);

      //Only show the other cycles link if there are other published visible cycles
      $applications = $this->_em->getRepository('Jazzee\Entity\Application')->findByProgram($this->program, false, true, array($this->application->getId()));
      if(count($applications) > 0){
        $link = new \Foundation\Navigation\Link('Other Cycles');
        $link->setHref($this->path('apply/' . $this->program->getShortName()));
        $menu->addLink($link);
      }
      $link = new \Foundation\Navigation\Link('Returning Applicants');
      $link->setHref($this->path($path . '/applicant/login'));
      $menu->addLink($link);
      if(!$this->application->isByInvitationOnly()){
        $link = new \Foundation\Navigation\Link('Start a New Application');
        $link->addClass('highlight');
        $link->setHref($this->path($path . '/applicant/new'));
        $menu->addLink($link);
      }
    }
    $navigation->addMenu($menu);

    if($this->isPreviewMode()){
      $menu = new \Foundation\Navigation\Menu();
      $navigation->addMenu($menu);

      $menu->setTitle('Preview Functions');
      $link = new \Foundation\Navigation\Link('Become Administrator');
      $link->setHref($this->path('admin/login'));
      $menu->addLink($link);
    }

    return $navigation;
  }

}
