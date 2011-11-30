<?php
ini_set('memory_limit', '1G');
ini_set('max_execution_time', 600);
/**
 * Run admin cron tasks
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 */
class AdminCronController extends \Jazzee\AdminController {
  /**
   * The maximum time a cron semephor can stick around before throwing an exception and deleting itself
   */
  const MAX_INTERVAL = 86400;
  const REQUIRE_AUTHORIZATION = false;
  const REQUIRE_APPLICATION = false;
  
  public function actionIndex(){
    $startTime = time();
    if(!$this->semaphore()){
      throw new Exception('Cron tried to run, but the semephore was still set');
    }
    
    foreach($this->listControllers() as $controller){
      \Foundation\VC\Config::includeController($controller);
      $class = \Foundation\VC\Config::getControllerClassName($controller);
      if(method_exists($class, 'runCron')){
        $class::runCron($this);
      }   
    }
    
    foreach($this->_em->getRepository('\Jazzee\Entity\PageType')->findAll() as $pageType){
      $class = $pageType->getClass();
      if(method_exists($class, 'runCron')){
        $class::runCron($this);
      }   
    }
    //Perform and applicant actions
    \Foundation\VC\Config::includeController('apply_applicant');
    ApplyApplicantController::runCron($this);

    
    //clear the semephore
    $this->setVar('adminCronSemephore', false);
    //cron outputs nothing
    $this->_em->flush();
    exit(0);
	}
  
/**
   * Get value from the cron store
   * @param string $name
   */
  public function getVar($name){
    if($var = $this->_em->getRepository('\Jazzee\Entity\CronVariable')->findOneBy(array('name'=>$name))){
      return $var->getValue();
    }
    return false;
  }
  
  /**
   * Set value to the cron store
   * @param string $name
   * @param mixed $value
   */
  public function setVar($name, $value){
    if(!$var = $this->_em->getRepository('\Jazzee\Entity\CronVariable')->findOneBy(array('name'=>$name))){
      $var = new \Jazzee\Entity\CronVariable();
      $var->setName($name);
      $var->setValue($value);
      $this->_em->persist($var);
      $this->_em->flush();
    }
    $var->setValue($value);
    $this->_em->persist($var);
    return true;
  }
  
  protected function semaphore(){
    $semephore = $this->getVar('adminCronSemephore');
    $lastRun = $this->getVar('adminCronLastRun');
    if(!empty($semephore)){
      //attempting to run cron again too soon
      if(time() - (int)$lastRun < self::MAX_INTERVAL) return false;
      
      //This semephore has been around for too long delete it and throw an exception so the next run can proceed normally
      $this->setVar('adminCronSemephore', false);
      trigger_error('AdminCron semephore was set last at ' . date('r',$lastRun) .  ' which was more than ' . self::MAX_INTERVAL . ' seconds ago.  This can indicate a broken cron process.', E_USER_NOTICE);
    }
    $this->setVar('adminCronSemephore', true);
    $this->setVar('adminCronLastRun', time());
    return true;
  }
}

?>