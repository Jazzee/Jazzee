<?php

namespace Jazzee;

/**
 * Base controller for all apply controllers
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
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
   * Session Store
   * @var \Jazzee\Session\Store
   */
  protected $_store;

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
      exit();
    }
  }

  /**
   * Check credentials and intialize members
   */
  public function beforeAction()
  {
    parent::beforeAction();
    $this->_store = $this->_session->getStore('apply', $this->_config->getApplicantSessionLifetime());
    $this->_application = $this->_em->getRepository('Jazzee\Entity\Application')->findEasy($this->actionParams['programShortName'], $this->actionParams['cycleName']);
    if (!$this->_application) {
      throw new \Jazzee\Exception("Unable to load {$this->actionParams['programShortName']} {$this->actionParams['cycleName']} application", E_USER_NOTICE, 'That is not a valid application');
    }
    if (!$this->_application->isPublished() or $this->_application->getOpen() > new \DateTime('now')) {
      $this->addMessage('error', $this->_application->getCycle()->getName() . ' ' . $this->_application->getProgram()->getName() . ' is not open for applicants');
      $this->redirectPath('apply/' . $this->_application->getProgram()->getShortName());
    }
    foreach ($this->_application->getApplicationPages(\Jazzee\Entity\ApplicationPage::APPLICATION) as $applicationPage) {
      $this->_pages[$applicationPage->getId()] = $applicationPage;
      $applicationPage->getJazzeePage()->setController($this);
    }
    $this->setLayoutVar('layoutTitle', $this->_application->getCycle()->getName() . ' ' . $this->_application->getProgram()->getName() . ' Application');
    $this->addCss($this->path('resource/styles/apply.css'));
    $this->setVar('application', $this->_application);
  }

  /**
   * Add the programa and cycle to the path
   * @param string $path
   * @return string
   */
  public function applyPath($path)
  {
    return $this->path('apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/' . $path);
  }

  /**
   * Add the programa and cycle to the path
   * @param string $path
   * @return string
   */
  public function absoluteApplyPath($path)
  {
    return $this->absolutePath('apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/' . $path);
  }

  /**
   * Redirect from an appliy path
   * @param string $path
   * @return string
   */
  public function redirectApplyPath($path)
  {
    $this->redirectPath('apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/' . $path);
  }

  /**
   * Redirect Applicant to the first page of the application
   */
  public function redirectApplyFirstPage()
  {
    reset($this->_pages);
    $first = key($this->_pages);
    $this->redirectApplyPath('page/' . $first);
  }

}