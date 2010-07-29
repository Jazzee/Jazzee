<?php
/**
 * ApplicationPage
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 */
class ApplicationPage extends BaseApplicationPage{
  
  /**
   * returns a value of a property or a related component
   *
   * @param mixed $fieldName                  name of the property or related component
   * @param boolean $load                     whether or not to invoke the loading procedure
   * @throws Doctrine_Record_Exception        if trying to get a value of unknown property / related component
   * @return mixed
   */
  public function get($fieldName, $load = true){
    $fields = array('title', 'min', 'max', 'optional', 'instructions', 'leadingText', 'trailingText');
    if(in_array($fieldName, $fields)){
      if(is_null($this->_data[$fieldName])){
        return $this['Page']->$fieldName;
      }
    }
    return parent::get($fieldName, $load);
  }    
}