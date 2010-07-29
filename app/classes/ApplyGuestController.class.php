<?php
/**
 * Base controller for unauthenticated Apply controllers
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage apply
 */
class ApplyGuestController extends JazzeeController{
  /**
   * The program if it is available
   * @var Program
   */
  protected $program;
  
  /**
   * The application if it is available
   * @var Application
   */
  protected $application;
  
  /**
   * Before any action do some setup
   * Set the navigation to horizontal
   * If we know the program and cycle load the applicant var
   * If we only know the program fill that in
   * @return null
   */
  protected function beforeAction(){
    parent::beforeAction();
    if(!empty($this->actionParams['programShortName']) AND !empty($this->actionParams['cycleName'])){
      if(!$this->application = Application::findOneApplication($this->actionParams['programShortName'],$this->actionParams['cycleName'])){
        throw new Exception("{$this->actionParams['programShortName']} is not a valid program");
      }
    } else if(!empty($this->actionParams['programShortName'])){
      if(!$this->program = Doctrine::getTable('Program')->findOneByShortName($this->actionParams['programShortName'])){
        throw new Exception("{$this->actionParams['programShortName']} is not a valid program");
      }
    }
  }
}
?>