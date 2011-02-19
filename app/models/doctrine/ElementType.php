<?php
/**
 * ElementType
 * 
 * @property string $name
 * @property string $class
 * @property Doctrine_Collection $Element
 * 
 * @package    jazzee
 * @subpackage orm
 */
class ElementType extends Doctrine_Record{
  
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('element_type');
    $this->hasColumn('name', 'string', 255, array(
      'type' => 'string',
      'notnull' => true,
      'unique' => true,
      'length' => '255',
     ));
    $this->hasColumn('class', 'string', 255, array(
      'type' => 'string',
      'notnull' => true,
      'unique' => true,
      'length' => '255',
     ));
  }

  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasMany('Element', array(
      'local' => 'id',
       'foreign' => 'elementType')
    );
  }
}