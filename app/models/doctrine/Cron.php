<?php
/**
 * Cron
 * @property integer $id
 * @property string $name
 * @property string $value
 * 
 * @package    jazzee
 * @subpackage orm
 */
class Cron extends Doctrine_Record{
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('cron');
    $this->hasColumn('name', 'string', 255, array(
      'type' => 'string',
      'notnull' => true,
      'unique' => true,
      'length' => '255',
     ));
    $this->hasColumn('value', 'string', 3000, array(
      'type' => 'string',
      'length' => '3000',
     ));
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