<?php
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
      exit(1);
    }
    foreach($this->listControllers() as $className){
      FoundationVC_Config::includeController($className);
      $class = FoundationVC_Config::getControllerClassName($className);
      if(call_user_func(array($class, 'runCron'),$this->get("{$className}lastRun"))){
        $this->set("{$className}lastRun", time());
      }
    }
    //clear the semephore
    $this->set('adminCronSemephore', false);
    //cron outputs nothing
    exit(0);
	}
  
/**
   * Get value from the cron store
   * @param string $name
   */
  protected function get($name){
    if($cron = Doctrine::getTable('Cron')->findOneByName($name)){
      return $cron->value;
    }
    return false;
  }
  
  /**
   * Set value to the cron store
   * @param string $name
   * @param mixed $value
   */
  protected function set($name, $value){
    if(!$cron = Doctrine::getTable('Cron')->findOneByName($name)){
      $cron = new Cron;
      $cron->name = $name;
    }
    $cron->value = $value;
    $cron->save();
    return true;
  }
  
  protected function semaphore(){
    $semephore = $this->get('adminCronSemephore');
    $lastRun = $this->get('adminCronLastRun');
    if(!empty($semephore)){
      //attempting to run cron again too soon
      if(time() - (int)$lastRun < self::MAX_INTERVAL) return false;
      
      //This semephore has been around for too long delete it and throw an exception so the next run can proceed normally
      $this->set('adminCronSemephore', false);
      throw new Jazzee_Exception('AdminCron semephore was set last at ' . date('r',$lastRun) .  ' which was more than ' . self::MAX_INTERVAL . ' seconds ago.  This can indicate a broken cron process.', E_ERROR);
    }
    $this->set('adminCronSemephore', true);
    $this->set('adminCronLastRun', time());
    
    return true;
  }
}

?>