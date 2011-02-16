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
   * Override the get method to check the Page when nothing is set in ApplicationPage
   *
   * @param mixed $fieldName
   * @param boolean $load whether or not to invoke the loading procedure
   * @return mixed
   */
  public function get($fieldName, $load = true){
    $fields = array('title', 'min', 'max', 'optional', 'instructions', 'leadingText', 'trailingText');
    if(in_array($fieldName, $fields)){
      if(is_null($this->_data[$fieldName]) or is_a($this->_data[$fieldName], 'Doctrine_Null')){
        return $this->Page->$fieldName;
      }
    }
    return parent::get($fieldName, $load);
  }  
  
  /**
   * Set the title
   * If this isn't a global page then store the title in Page and not here
   * @param string $value
   */
  public function setTitle($value){
    if(!$this->Page->isGlobal){
      return $this->Page->title = $value;
    }
    return $this->_set('title',$value);
  }
  
/**
   * Set the min
   * If this isn't a global page then store the min in Page and not here
   * @param string $value
   */
  public function setMin($value){
    if(!$this->Page->isGlobal){
      return $this->Page->min = $value;
    }
    return $this->_set('min',$value);
  }
  
/**
   * Set the max
   * If this isn't a global page then store the max in Page and not here
   * @param string $value
   */
  public function setMax($value){
    if(!$this->Page->isGlobal){
      return $this->Page->max = $value;
    }
    return $this->_set('max',$value);
  }
  
/**
   * Set the optional
   * If this isn't a global page then store the optional in Page and not here
   * @param string $value
   */
  public function setOptional($value){
    if(!$this->Page->isGlobal){
      return $this->Page->optional = $value;
    }
    return $this->_set('optional',$value);
  }
  
/**
   * Set the instructions
   * If this isn't a global page then store the instructions in Page and not here
   * @param string $value
   */
  public function setInstructions($value){
    if(!$this->Page->isGlobal){
      return $this->Page->instructions = $value;
    }
    return $this->_set('instructions',$value);
  }
  
/**
   * Set the leadingText
   * If this isn't a global page then store the title in Page and not here
   * @param string $value
   */
  public function setLeadingText($value){
    if(!$this->Page->isGlobal){
      return $this->Page->leadingText = $value;
    }
    return $this->_set('leadingText',$value);
  }
  
/**
   * Set the trailingText
   * If this isn't a global page then store the title in Page and not here
   * @param string $value
   */
  public function setTrailingText($value){
    if(!$this->Page->isGlobal){
      return $this->Page->trailingText = $value;
    }
    return $this->_set('trailingText',$value);
  }
  
  /**
   * After we save the applicationPage make sure all of its pages are properly saved too
   * At some point doctrine is unable to follow the relationships deep enough
   * This method explicitly saves the members of collections with the correct id
   */
  public function postSave(){
    if($this->Page->isModified(true)){
      $this->Page->save();
    }
  }
}