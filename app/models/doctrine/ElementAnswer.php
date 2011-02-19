<?php

/**
 * ElementAnswer
 * 
 * @property integer $answerID
 * @property integer $elementID
 * @property integer $position
 * @property string $eShortString
 * @property string $eLongString
 * @property timestamp $eDate
 * @property integer $eInteger
 * @property decimal $eDecimal
 * @property blob $eBlob
 * @property Answer $Answer
 * @property Element $Element
 * 
 * @package    jazzee
 * @subpackage orm
 */
class ElementAnswer extends Doctrine_Record{
  
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('element_answer');
    $this->hasColumn('answerID', 'integer', null, array(
      'type' => 'integer',
      'primary' => true,
     ));
    $this->hasColumn('elementID', 'integer', null, array(
      'type' => 'integer',
      'primary' => true,
     ));
    $this->hasColumn('position', 'integer', null, array(
      'type' => 'integer',
      'primary' => true,
     ));
    $this->hasColumn('eShortString', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
     ));
    $this->hasColumn('eLongString', 'string', 10000, array(
      'type' => 'string',
      'length' => '10000',
     ));
    $this->hasColumn('eDate', 'timestamp', null, array(
      'type' => 'timestamp',
     ));
    $this->hasColumn('eInteger', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('eDecimal', 'decimal', null, array(
      'type' => 'decimal',
     ));
    $this->hasColumn('eBlob', 'blob', null, array(
      'type' => 'blob',
     ));
  }

  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasOne('Answer', array(
      'local' => 'answerID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );

    $this->hasOne('Element', array(
      'local' => 'elementID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );
  }
}