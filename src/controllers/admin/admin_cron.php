<?php

/**
 * Run admin cron tasks
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class AdminCronController extends \Jazzee\AdminController
{
  /**
   * The maximum time a cron semephor can stick around before throwing an exception and deleting itself
   */

  const MAX_INTERVAL = 3600;
  const REQUIRE_AUTHORIZATION = false;
  const REQUIRE_APPLICATION = false;
  const MAX_EXECUTION_TIME = 600;
  const MEMORY_LIMIT = '2G';
  const VERBOSE_LOGS = true;

  /**
   * Check to see if this host is allowed to run cron
   */
  protected function setUp()
  {
    $this->layout = 'blank';
    if ($this->_config->getAdminCronAllowed()) {
      $allowedIps = array();
      foreach (\explode(',', $this->_config->getAdminCronAllowed()) as $value) {
        if (!empty($value) AND $resolvedIps = \gethostbynamel($value)) {
          $allowedIps = array_merge($allowedIps, $resolvedIps);
        }
      }
      $hostname = \gethostbyaddr($_SERVER['REMOTE_ADDR']);
      $allowed = false;
      foreach (\gethostbynamel($hostname) as $ip) {
        if (in_array($ip, $allowedIps)) {
          $allowed = true;
        }
      }
      if (!$allowed) {
        throw new \Jazzee\Exception('Client ' . $hostname . ' resolved to ips ' . implode(',', \gethostbynamel($hostname)) . ' which are not allowed to access cron.');
      }
    }
  }

  public function actionIndex()
  {
    if ($this->semaphore()) {
      $this->setLimits();
      $this->log('Cron run started');
      foreach ($this->listControllers() as $controller) {
        \Foundation\VC\Config::includeController($controller);
        $class = \Foundation\VC\Config::getControllerClassName($controller);
        if (method_exists($class, 'runCron')) {
          if(self::VERBOSE_LOGS){
            $this->log("Admin controller {$controller} job started");
          }
          $class::runCron($this);
          $this->_em->flush();
        }
        //reset the max execution time and memory limit after every admin script is included because some override this
        $this->setLimits();
      }

      foreach ($this->_em->getRepository('\Jazzee\Entity\PageType')->findAll() as $pageType) {
        $class = $pageType->getClass();
        if (method_exists($class, 'runCron')) {
          if(self::VERBOSE_LOGS){
            $this->log("Page type {$class} job started");
          }
          $class::runCron($this);
          $this->_em->flush();
        }
        $this->setLimits();
      }

      foreach ($this->_em->getRepository('\Jazzee\Entity\ElementType')->findAll() as $elementType) {
        $class = $elementType->getClass();
        if (method_exists($class, 'runCron')) {
          if(self::VERBOSE_LOGS){
            $this->log("Element type {$class} job started");
          }
          $class::runCron($this);
          $this->_em->flush();
        }
        $this->setLimits();
      }

      //Perform applicant actions
      \Foundation\VC\Config::includeController('apply_applicant');
      if(self::VERBOSE_LOGS){
        $this->log("Controller apply_applicant job started");
      }
      ApplyApplicantController::runCron($this);

      //File store actions
      \Jazzee\FileStore::runCron($this);

      //clear the semephore
      $this->setVar('adminCronSemephore', false);
      $this->log('Cron run finished');
    }
  }

  /**
   * Get value from the cron store
   * @param string $name
   */
  public function getVar($name)
  {
    if ($var = $this->_em->getRepository('\Jazzee\Entity\CronVariable')->findOneBy(array('name' => $name))) {
      return $var->getValue();
    }

    return false;
  }

  /**
   * Set value to the cron store
   * @param string $name
   * @param mixed $value
   */
  public function setVar($name, $value)
  {
    if (!$var = $this->_em->getRepository('\Jazzee\Entity\CronVariable')->findOneBy(array('name' => $name))) {
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

  protected function semaphore()
  {
    $semephore = $this->getVar('adminCronSemephore');
    $lastRun = $this->getVar('adminCronLastRun');
    if (!empty($semephore)) {
      //attempting to run cron again too soon
      if (time() - (int) $lastRun < self::MAX_INTERVAL) {
        $this->log('AdminCron semephore was set last at ' . date('r', $lastRun) . ' and cron is being run again at ' . date('r') . '.', \Monolog\Logger::ERROR);

        return false;
      }
      //This semephore has been around for too long delete it and throw an exception so the next run can proceed normally
      $this->setVar('adminCronSemephore', false);
      $this->log('AdminCron semephore was set last at ' . date('r', $lastRun) . ' which was more than ' . self::MAX_INTERVAL . ' seconds ago.  This can indicate a broken cron process.  The semaphore has been reset so cron can run again.', \Monolog\Logger::ERROR);
    }
    $this->setVar('adminCronSemephore', true);
    $this->setVar('adminCronLastRun', time());

    return true;
  }

  /**
   * Set the execution and memory limit for the script
   */
  protected function setLimits()
  {
    ini_set('memory_limit', self::MEMORY_LIMIT);
    set_time_limit(self::MAX_EXECUTION_TIME);
  }

}