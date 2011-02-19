<?php
/**
 * StatusType
 * Normalize the answer status types
 * @property string $name
 * @property Doctrine_Collection $Answer
 * 
 * @package    jazzee
 * @subpackage orm
 */
class StatusType extends Doctrine_Record{
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('status_type');
    $this->hasColumn('name', 'string', 255, array(
    	'type' => 'string',
      'length' => '255',
    ));
  }
  
  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasMany('Answer', array(
     'local' => 'id',
     'foreign' => 'publicStatus')
    );
    $this->hasMany('Answer', array(
     'local' => 'id',
     'foreign' => 'privateStatus')
    );
  }
}