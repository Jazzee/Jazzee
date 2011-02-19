<?php
/**
 * UserRole
 * Many2Many connecter for Users and Roles
 * @property integer $userID
 * @property integer $roleID
 * @property User $User
 * @property Role $Role
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 */
class UserRole extends Doctrine_Record{
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('user_role');
    $this->hasColumn('userID', 'integer', null, array(
      'type' => 'integer',
    ));
    $this->hasColumn('roleID', 'integer', null, array(
      'type' => 'integer',
    ));
  }
  
  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasOne('User', array(
      'local' => 'userID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );

    $this->hasOne('Role', array(
      'local' => 'roleID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );
  }
}