<?php
/**
 * ElementListItem
 * 
 * @property integer $elementID
 * @property integer $weight
 * @property boolean $active
 * @property string $value
 * @property Element $Element
 * 
 * @package    jazzee
 * @subpackage orm
 */
class ElementListItem extends Doctrine_Record{
  
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('element_list_item');
    $this->hasColumn('elementID', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('weight', 'integer', null, array(
      'type' => 'integer',
      'default' => 0,
     ));
    $this->hasColumn('active', 'boolean', null, array(
      'type' => 'boolean',
      'default' => true,
     ));
    $this->hasColumn('value', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
     ));
  }

  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasOne('Element', array(
      'local' => 'elementID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );
  }
}