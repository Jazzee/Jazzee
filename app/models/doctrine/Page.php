<?php
/**
 * Page
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 */
class Page extends BasePage{
  public function getElementByTitle($title){
    foreach($this['Elements'] as $element){
      if($element->title == $title){return $element;}
    }
    return self::$_null;
  }
  
  /**
   * Find an element by ID
   * @param integer $id
   */
  public function getElementById($id){
    $key = array_search($id, $this->Elements->getPrimaryKeys());
    if($key !== false){ //use === becuase 0 is returned often
      return $this->Elements->get($key);
    }
    return false;
  }
  
  /**
   * Get Variable by name
   * @param string $name
   * @return blob || NULL
   */
  public function getVar($name){
    foreach($this['Variables'] as $variable)
      if($variable->name == $name)return $variable->value;
    return self::$_null;
  }
  
  /**
   * Set page variable
   * @param string $name
   * @param string $value
   */
  public function setVar($name, $value){
    foreach($this['Variables'] as $variable)
      if($variable->name == $name)return $variable->value = $value;
    //create a new empty variable with that name
    $var = $this->Variables->get(null);
    $var->name = $name;
    $var->value = $value;
  }
  
  /**
   * Find child page by ID
   * @param integer $id
   * @return Page || NULL
   */
  public function getChildById($id){
    foreach($this->Children as $child){
      if($child->id == $id) return $child;
    }
    return false;
  }
  
  /**
   * After we save the Page make sure all of its elements and children are saved too
   * At some point doctrine is unable to follow the relationships deep enough
   * This method explicitly saves the members of collections with the correct id
   */
  public function postSave(){
    foreach($this->Elements as $element){
      if($element->isModified(true)){
        $element->pageID = $this->id;
        $element->save();
      }
    }
    foreach($this->Children as $child){
      if($child->isModified(true)){
        $child->parentID = $this->id;
        $child->save();
      }
    }
  foreach($this->Variables as $variable){
      if($variable->isModified(true)){
        $variable->pageID = $this->id;
        $variable->save();
      }
    }
  }
}