<?php
/**
 * Welcome and information for un-authenticated applicants
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
class ApplyWelcomeController extends ApplyGuestController {
  
  public function actionIndex($programShortName = '', $cycleName = '') {
    if(empty($this->program) AND empty($this->application)){
      $q = Doctrine_Query::create()
        ->select('p.*, SUM(a.live) as liveSum, SUM(a.visible) as visibleSum')
        ->from('Program p, Application a')
        ->orderBy('p.name')
        ->where('p.expires IS NULL OR p.expires > now()')
        ->andWhere('p.id = a.programid')
        ->groupBy('a.programid');     
      $this->setVar('programList', $q->fetchArray());
      $this->loadView($this->controllerName . '/programs');
      return true;
    }
    if(empty($this->application)){
      $this->setLayoutVar('layoutTitle', $this->program->name . ' Application');
      $this->setVar('program', $this->program);
      $this->loadView($this->controllerName . '/cycles');
      return true;
    }
    $this->setLayoutVar('layoutTitle', $this->application['Cycle']->name . ' ' . $this->application['Program']->name . ' Application');
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
