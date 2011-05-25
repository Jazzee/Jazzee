<?php
/**
 * Welcome and information for un-authenticated applicants
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
class ApplyWelcomeController extends \Jazzee\Controller {
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
   * Before any action do some setup
   * If we know the program and cycle load the applicant var
   * If we only know the program fill that in
   * @return null
   */
  protected function beforeAction(){
    parent::beforeAction();
    if(!empty($this->actionParams['programShortName'])) $this->program = $this->_em->getRepository('Jazzee\Entity\Program')->findOneByShortName($this->actionParams['programShortName']);
    if(!empty($this->actionParams['cycleName'])) $this->cycle = $this->_em->getRepository('Jazzee\Entity\Cycle')->findOneByName($this->actionParams['cycleName']);
    if(!is_null($this->program) AND !is_null($this->cycle)) $this->application = $this->_em->getRepository('Jazzee\Entity\Application')->findOneByProgramAndCycle($this->program,$this->cycle);
    $this->setLayoutVar('navigation', $this->getNavigation());
  }
  
  /**
   * Display welcome information
   * Might display list of program, cycles in a program, or an application welcome page dependign on the url
   * Enter description here ...
   */
  public function actionIndex() {
    if(is_null($this->program)){
      $this->setVar('programs', $this->_em->getRepository('Jazzee\Entity\Program')->findAllActive());
      $this->setLayoutVar('layoutTitle', 'Select a Program');
      $this->loadView($this->controllerName . '/programs');
      return true;
    }
    if(empty($this->application)){
      $this->setLayoutVar('layoutTitle', $this->program->getName() . ' Application');
      $this->setVar('applications',$this->_em->getRepository('Jazzee\Entity\Application')->findByProgram($this->program));
      $this->loadView($this->controllerName . '/cycles');
      return true;
    }
    if(!$this->application->isPublished()){
      $this->redirectPath('apply/' . $this->application->getProgram()->getShortName() . '/');
    }
    $this->setLayoutVar('layoutTitle', $this->cycle->getName() . ' ' . $this->program->getName() . ' Application');
    $this->setVar('application', $this->application);
  }
  
  /**
   * Get the navigation
   * @return Navigation
   */
  public function getNavigation(){
    if(empty($this->program) AND empty($this->application)){
      return null;
    }
    $navigation = new \Foundation\Navigation\Container();
    $menu = new \Foundation\Navigation\Menu();
    
    $menu->setTitle('Navigation');
    if(empty($this->application)){
      $link = new \Foundation\Navigation\Link('Welcome');
      $link->setHref($this->path('apply'));
      $menu->addLink($link);
    } else {
      $path = 'apply/' . $this->program->getShortName() . '/' . $this->cycle->getName();
      $link = new \Foundation\Navigation\Link('Welcome');
      $link->setHref($this->path($path));
      $link->setCurrent(true);
      
      $menu->addLink($link); 
      $link = new \Foundation\Navigation\Link('Other Cycles');
      $link->setHref($this->path('apply/' . $this->program->getShortName() . '/'));
      $menu->addLink($link);
      
      $link = new \Foundation\Navigation\Link('Returning Applicants');
      $link->setHref($this->path($path . '/applicant/login/'));
      $menu->addLink($link);
      
      $link = new \Foundation\Navigation\Link('Start a New Application');
      $link->setHref($this->path($path . '/applicant/new/'));
      $menu->addLink($link);
    }
    $navigation->addMenu($menu);
    return $navigation;
  }
}
?>
