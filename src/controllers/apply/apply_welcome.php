<?php
/**
 * Welcome and information for un-authenticated applicants
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
class ApplyWelcomeController extends JazzeeController {
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
    if(!empty($this->actionParams['programShortName'])) $this->program = Doctrine::getTable('Program')->findOneByShortName($this->actionParams['programShortName']);
    if(!empty($this->actionParams['cycleName'])) $this->cycle = Doctrine::getTable('Cycle')->findOneByName($this->actionParams['cycleName']);
    
    if(!is_null($this->program) AND !is_null($this->cycle)) $this->application = Doctrine::getTable('Application')->findOneByProgramIDAndCycleID($this->program->id, $this->cycle->id);
    
  }
  
  /**
   * Display welcome information
   * Might display list of program, cycles in a program, or an application welcome page dependign on the url
   * Enter description here ...
   */
  public function actionIndex() {
    if(is_null($this->program)){  
      $arr = Doctrine::getTable('Program')->findAll(Doctrine_Core::HYDRATE_ARRAY);
      $programs = array();
      foreach($arr as $p){
        if(is_null($p['expires']) or strtotime($p['expires']) > time()) $programs[$p['shortName']] = $p['name'];
      }
      $this->setVar('programs', $programs);
      $this->setLayoutVar('layoutTitle', 'Select a Program');
      $this->loadView($this->controllerName . '/programs');
      return true;
    }
    if(empty($this->application)){
      $this->setLayoutVar('layoutTitle', $this->program->name . ' Application');
      $this->setVar('program', $this->program);
      $this->loadView($this->controllerName . '/cycles');
      return true;
    }
    if(!$this->application->published){
      $this->redirectPath("apply/{$this->application->Program->shortName}/");
    }
    $this->setLayoutVar('layoutTitle', $this->application->Cycle->name . ' ' . $this->application->Program->name . ' Application');
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
    $navigation = new Navigation();
    $menu = $navigation->newMenu();
    $menu->title = 'Navigation';
    if(empty($this->application)){
      $menu->newLink(array('text'=>'List of Programs', 'href'=>$this->path('/apply')));
    } else {
      $menu->newLink(array('text'=>'Welcome', 'href'=>$this->path("apply/{$this->application['Program']->shortName}/{$this->application['Cycle']->name}/"), 'current'=>true));
      $menu->newLink(array('text'=>'Other Cycles', 'href'=>$this->path("apply/{$this->application['Program']->shortName}/")));
      $menu->newLink(array('text'=>'Returning Applicants', 'href'=>$this->path("apply/{$this->application['Program']->shortName}/{$this->application['Cycle']->name}/applicant/login/")));
      $menu->newLink(array('text'=>'Start a New Application', 'href'=>$this->path("apply/{$this->application['Program']->shortName}/{$this->application['Cycle']->name}/applicant/new/")));
    }
    return $navigation;
  }
}
?>
