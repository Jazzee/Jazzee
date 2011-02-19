<?php
/**
 * Cycle
 * 
 * @property string $name
 * @property timestamp $start
 * @property timestamp $end
 * @property Doctrine_Collection $Application
 * @property Doctrine_Collection $DefaultUsers
 * 
 * @package    jazzee
 * @subpackage orm
 */
class Cycle extends Doctrine_Record{
  
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('cycle');
    $this->hasColumn('name', 'string', 50, array(
      'type' => 'string',
      'notnull' => true,
      'unique' => true,
      'nospace' => true,
      'length' => '50',
     ));
    $this->hasColumn('start', 'timestamp', null, array(
      'type' => 'timestamp',
     ));
    $this->hasColumn('end', 'timestamp', null, array(
      'type' => 'timestamp',
     ));

     $this->check('end > start');
  }

  public function setUp(){
    parent::setUp();
    $this->hasMany('Application', array(
     'local' => 'id',
     'foreign' => 'cycleID')
    );

    $this->hasMany('User as DefaultUsers', array(
      'local' => 'id',
      'foreign' => 'defaultCycle')
    );
  }
}