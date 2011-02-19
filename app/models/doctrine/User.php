<?php
/**
 * User
 * System users
 * @property string $campusID
 * @property string $email
 * @property string $password
 * @property string $activateToken
 * @property string $firstName
 * @property string $lastName
 * @property timestamp $expires
 * @property timestamp $lastLogin
 * @property string $lastLogin_ip
 * @property string $lastFailedLogin_ip
 * @property integer $failedLoginAttempts
 * @property string $apiKey
 * @property blob $privateSSLKey
 * @property blob $publicSSLKey
 * @property blob $globalPrivateSSLKey
 * @property integer $defaultProgram
 * @property integer $defaultCycle
 * @property Program $Program
 * @property Cycle $Cycle
 * @property Doctrine_Collection $Communication
 * @property Doctrine_Collection $Roles
 * @property array $allowedActions
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 */
class User extends Doctrine_Record{
  protected $allowedActions;
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('user');
    $this->hasColumn('campusID', 'string', 60, array(
      'type' => 'string',
      'unique' => true,
      'length' => '60',
    ));
    $this->hasColumn('email', 'string', 255, array(
      'type' => 'string',
      'notnull' => true,
      'notblank' => true,
      'unique' => true,
      'length' => '255',
    ));
    $this->hasColumn('password', 'string', 60, array(
      'type' => 'string',
      'length' => '60',
    ));
    $this->hasColumn('activateToken', 'string', 40, array(
      'type' => 'string',
      'unique' => true,
      'length' => '40',
    ));
    $this->hasColumn('firstName', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
    ));
    $this->hasColumn('lastName', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
    ));
    $this->hasColumn('expires', 'timestamp', null, array(
      'type' => 'timestamp',
    ));
    $this->hasColumn('lastLogin', 'timestamp', null, array(
      'type' => 'timestamp',
    ));
    $this->hasColumn('lastLogin_ip', 'string', 15, array(
      'type' => 'string',
      'ip' => true,
      'length' => '15',
    ));
    $this->hasColumn('lastFailedLogin_ip', 'string', 15, array(
      'type' => 'string',
      'ip' => true,
      'length' => '15',
    ));
    $this->hasColumn('failedLoginAttempts', 'integer', 2, array(
      'type' => 'integer',
      'length' => '2',
    ));
    $this->hasColumn('apiKey', 'string', 40, array(
      'type' => 'string',
      'unique' => true,
      'length' => '40',
    ));
    $this->hasColumn('privateSSLKey', 'blob', null, array(
      'type' => 'blob',
    ));
    $this->hasColumn('publicSSLKey', 'blob', null, array(
      'type' => 'blob',
    ));
    $this->hasColumn('globalPrivateSSLKey', 'blob', null, array(
      'type' => 'blob',
    ));
    $this->hasColumn('defaultProgram', 'integer', null, array(
      'type' => 'integer',
    ));
    $this->hasColumn('defaultCycle', 'integer', null, array(
      'type' => 'integer',
    ));
  }
  
  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasOne('Program', array(
      'local' => 'defaultProgram',
      'foreign' => 'id',
      'onDelete' => 'SET NULL',
      'onUpdate' => 'CASCADE')
    );

    $this->hasOne('Cycle', array(
      'local' => 'defaultCycle',
      'foreign' => 'id',
      'onDelete' => 'SET NULL',
      'onUpdate' => 'CASCADE')
    );

    $this->hasMany('Communication', array(
      'local' => 'id',
      'foreign' => 'userID')
    );

    $this->hasMany('UserRole as Roles', array(
      'local' => 'id',
      'foreign' => 'userID')
    );
  }
    
  
  
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