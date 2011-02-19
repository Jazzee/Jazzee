<?php

/**
 * RoleAction
 * @property integer $roleID
 * @property string $controller
 * @property string $action
 * @property Role $Role
 * 
 * @package    jazzee
 * @subpackage orm
 */
class RoleAction extends Doctrine_Record{
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('role_action');
    $this->hasColumn('roleID', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('controller', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
     ));
    $this->hasColumn('action', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
     ));
  }
  
  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasOne('Role', array(
      'local' => 'roleID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );
  }
}