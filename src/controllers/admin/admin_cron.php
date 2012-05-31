<?php
ini_set('memory_limit', '1G');
set_time_limit(600);
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
  const MAX_INTERVAL = 3600;
  const REQUIRE_AUTHORIZATION = false;
  const REQUIRE_APPLICATION = false;
  
  /**
   * Check to see if this host is allowed to run cron
   */
  protected function setUp() {
    if($this->_config->getAdminCronAllowed()){
      $allowedIps = array();
      foreach(\explode(',',$this->_config->getAdminCronAllowed()) as $value){
        if(!empty($value) AND $resolvedIps = \gethostbynamel($value)) 
          $allowedIps = array_merge($allowedIps, \gethostbynamel($value));
      }
      $hostname = \gethostbyaddr($_SERVER['REMOTE_ADDR']);
      $allowed = false;
      foreach(\gethostbynamel($hostname) as $ip){
        if(in_array($ip, $allowedIps)) $allowed = true;
      }
      if(!$allowed) throw new \Jazzee\Exception('Client ' . $hostname . ' resolved to ips ' . implode(',', \gethostbynamel($hostname)) . ' which are not allowed to access cron.');
    }
  }
  
  public function actionIndex(){
    if($this->semaphore()){
      $this->log('Cron run started');
      foreach($this->listControllers() as $controller){
        \Foundation\VC\Config::includeController($controller);
        $class = \Foundation\VC\Config::getControllerClassName($controller);
        if(method_exists($class, 'runCron')){
          $class::runCron($this);
          $this->_em->flush();
        }   
        //reset the max execution time and memory limit after every admin script is included because some override this
        set_time_limit(600);
        ini_set('memory_limit', '1G');
      }

      foreach($this->_em->getRepository('\Jazzee\Entity\PageType')->findAll() as $pageType){
        $class = $pageType->getClass();
        if(method_exists($class, 'runCron')){
          $class::runCron($this);
          $this->_em->flush();
        }
      }
      //Perform and applicant actions
      \Foundation\VC\Config::includeController('apply_applicant');
      ApplyApplicantController::runCron($this);


      //clear the semephore
      $this->setVar('adminCronSemephore', false);
      $this->log('Cron run finished');
    }
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
      if(time() - (int)$lastRun < self::MAX_INTERVAL){
        $this->log('AdminCron semephore was set last at ' . date('r',$lastRun) .  ' and cron is being run again at ' . date('r') . '.', \Monolog\Logger::ERROR);
        return false;
      }
      //This semephore has been around for too long delete it and throw an exception so the next run can proceed normally
      $this->setVar('adminCronSemephore', false);
      $this->log('AdminCron semephore was set last at ' . date('r',$lastRun) .  ' which was more than ' . self::MAX_INTERVAL . ' seconds ago.  This can indicate a broken cron process.  The semaphore has been reset so cron can run again.', \Monolog\Logger::ERROR);
    }
    $this->setVar('adminCronSemephore', true);
    $this->setVar('adminCronLastRun', time());
    return true;
  }
}

?>