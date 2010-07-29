<?php
/**
 * Base controller for all authenticated setup controllers
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
abstract class SetupController extends AdminController{
  public static function isAllowed($controller, $action, $user, $programID, $cycleID, $actionParams){
    if($programID AND $cycleID AND $user)  return $user->isAllowed($controller, $action, $programID);
    return false;
  }
}

?>