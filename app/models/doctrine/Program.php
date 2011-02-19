<?php

/**
 * Program
 * 
 * @property string $name
 * @property string $shortName
 * @property timestamp $expires
 * @property Doctrine_Collection $Applications
 * @property Doctrine_Collection $DefaultUsers
 * @property Doctrine_Collection $Roles
 * 
 * @package    jazzee
 * @subpackage orm
 */
class Program extends Doctrine_Record{
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('program');
    $this->hasColumn('name', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
     ));
    $this->hasColumn('shortName', 'string', 50, array(
      'type' => 'string',
      'notnull' => true,
      'nospace' => true,
      'notblank' => true,
      'unique' => true,
      'length' => '50',
     ));
    $this->hasColumn('expires', 'timestamp', null, array(
      'type' => 'timestamp',
     ));
  }
  
  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasMany('Application as Applications', array(
      'local' => 'id',
      'foreign' => 'programID')
    );

    $this->hasMany('User as DefaultUsers', array(
      'local' => 'id',
      'foreign' => 'defaultProgram')
    );

    $this->hasMany('Role as Roles', array(
      'local' => 'id',
      'foreign' => 'programID')
    );
}
}