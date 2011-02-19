<?php
/**
 * Role
 * 
 * @property string $name
 * @property boolean $global
 * @property integer $programID
 * @property Program $Program
 * @property Doctrine_Collection $Users
 * @property Doctrine_Collection $Actions
 * 
 * @package    jazzee
 * @subpackage orm
 */
class Role extends Doctrine_Record{
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('role');
    $this->hasColumn('name', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
     ));
    $this->hasColumn('global', 'boolean', null, array(
      'type' => 'boolean',
     ));
    $this->hasColumn('programID', 'integer', null, array(
      'type' => 'integer',
     ));
  }
  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasOne('Program', array(
      'local' => 'programID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );

    $this->hasMany('UserRole as Users', array(
      'local' => 'id',
      'foreign' => 'roleID')
    );

    $this->hasMany('RoleAction as Actions', array(
      'local' => 'id',
      'foreign' => 'roleID')
    );
  }
}