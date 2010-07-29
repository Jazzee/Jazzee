<?php
/**
 * Base controller for all authenticated applicants controllers
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
abstract class ApplicantsController extends AdminController{
  public static function isAllowed($controller, $action, $user, $programID, $cycleID, $actionParams){
    if($programID AND $cycleID AND $user)  return $user->isAllowed($controller, $action, $programID);
    return false;
  }
}

?>