<?php

/**
 * Output maintenance mode page
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class MaintenanceController extends \Jazzee\PageController
{
  public function actionIndex()
  {
    $this->layout = 'wide';
    header('HTTP/1.1 503 Service Unavailable');
    $this->setLayoutVar('layoutTitle', 'Down for Maintenance');
    if (!$message = $this->_config->getMaintenanceModeMessage()) {
      $message = 'Sorry for the inconvenience, the application is currently down for maintenance.';
    }
    $this->setVar('message', $message);
  }

}