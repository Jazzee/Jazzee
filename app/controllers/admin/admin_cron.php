<?php
/**
 * Run admin cron tasks
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 */
class AdminCronController extends AdminController {
  public function actionIndex(){
    $startTime = time();
    $lastRun = $this->getVariable('cron_lastRun');
    $semaphore = $this->getVariable('cron_semaphore');
    //dont run more often than every 30 minutes
    //dont ever run two jobs at once
    if(($lastRun and $startTime - $lastRun < 1800) or $semaphore){
      die('no run');
      exit(1);
    }
    $this->setVariable('cron_semaphore', 'true');
    $this->setVariable('cron_lastRun', $startTime);
    foreach($this->listControllers() as $className){
      Lvc_FoundationConfig::includeController($className);
      $class = Lvc_Config::getControllerClassName($className);
      if(call_user_func(array($class, 'runCron'),$this->getVariable("{$className}_cron_lastRun"))){
        $this->setVariable("{$className}_cron_lastRun", time());
      }
    }
    //cron outputs nothing
    exit(0);
	}
  
  public static function isAllowed($controller, $action, $user, $programID, $cycleID, $actionParams){
    return true; //everyone is allowed to use cron
  }
}

?>