<?php

/**
 * PageVariable
 * 
 * @property integer $pageID
 * @property string $name
 * @property string $value
 * @property Page $Page
 * 
 * @package    jazzee
 * @subpackage orm
 */
class PageVariable extends Doctrine_Record{
  
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('page_variable');
    $this->hasColumn('pageID', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('name', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
     ));
    $this->hasColumn('value', 'string', 1000, array(
      'type' => 'string',
      'length' => '1000',
     ));


    $this->index('page_variable', array(
      'fields' => array(
        0 => 'pageID',
        1 => 'name',
      ),
      'type' => 'unique',
     ));
  }

  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasOne('Page', array(
      'local' => 'pageID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );
}
  
  /**
   * Get the base64 decoded value
   * @return blob
   */
  public function getValue(){
    return base64_decode($this->_get('value'));
  }
  
  /**
   * Base64 encode the value
   * @param mixed $value
   * @return mixed
   */
  public function setValue($value){
    return $this->_set('value', base64_encode($value));
  }
}