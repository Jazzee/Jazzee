<?php
/**
 * Welcome Page for Administrative Functions
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 */
class AdminWelcomeController extends \Jazzee\AdminController {
  const TITLE = 'Home';
  const PATH = 'welcome';
  
  /**
   * Display index
   */
  public function actionIndex(){
    $this->setVar('firstName', $this->_user->getFirstName());
    $this->setVar('lastName', $this->_user->getLastName());
  }
  
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null){
    //Check to be sure a valid user object has been set
    //Any user is allowed access
    if($user){
      return true;
    }
    return false;
  }
}
?>