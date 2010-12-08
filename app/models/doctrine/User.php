<?php
/**
 * User
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 */
class User extends BaseUser{
  protected $allowedActions;
  
  /**
   * Constrcutor
   * Build the list of allowed actions
   */
  public function construct(){
    $this->allowedActions = array();
    $this->allowedActions['global'] = array();
    
    foreach($this->Roles as $role){
      if($role->Role->global){
        $key = 'global';
      } else {
        $key = $role->Role->programID;
      }
      foreach($role->Role->Actions as $action){
        if(!isset($this->allowedActions[$key][$action->controller]))
          $this->allowedActions[$key][$action->controller] = array();
        $this->allowedActions[$key][$action->controller][] = $action->action;
      }
    } 
  }
  
  /**
   * Hash and then store the password 
   * @param string $value the user input password
   */
  public function setPassword($value){
    $p = new PasswordHash(8, FALSE);
    $this->_set('password',$p->HashPassword($value));
  }
  
  /**
   * Store the previously hashed version of the password
   * @param string $value the user input password
   */
  public function setHashedPassword($value){
    $this->_set('password',$value);
  }
    
  /**
   * Check a password against its hash
   * @param string $password
   * @param string $hashedPassword
   */
  public function checkPassword($password){
    $p = new PasswordHash(8, FALSE);
    return $p->CheckPassword($password, $this->password);
  }
  
  /**
   * Check to see if a user is allowed to perform an action
   * @param string $controller
   * @param string $action
   * @param integer $programID
   * @return boolean
   */
  public function isAllowed($controller, $action, $programID = false){
    if(array_key_exists($controller, $this->allowedActions['global'])){
      if(in_array($action, $this->allowedActions['global'][$controller])){
        return true;
      }
    }
    if($programID){
      if(array_key_exists($programID, $this->allowedActions)){
        if(array_key_exists($controller, $this->allowedActions[$programID])){
          if(in_array($action, $this->allowedActions[$programID][$controller])){
            return true;
          }
        }
      }
    }
    return false;
  }
}