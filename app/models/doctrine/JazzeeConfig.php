<?php
/**
 * JazzeeConfig
 * 
 * @property string $name
 * @property blob $value
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 */
class JazzeeConfig extends Doctrine_Record{
  
  /**
   * @see BaseJazzeeConfig::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('jazzee_config');
    $this->hasColumn('name', 'string', 255, array(
      'type' => 'string',
      'notnull' => true,
      'unique' => true,
      'length' => '255',
     ));
    $this->hasColumn('value', 'blob', null, array(
      'type' => 'blob',
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