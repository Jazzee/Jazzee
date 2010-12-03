<?php
/**
 * The abstract list class
 * Select, Radio, Checkbox all descend from here
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
abstract class Form_ListElement extends Form_Element{
  private $_items = array();
  
  /**
   * Constructor
   * Add a list item validator
   * @param Form_Field $field
   */
  public function __construct($field){
    parent::__construct($field);
    $this->addValidator('ChoiceInList'); 
  }
  
  /**
   * Add a list item
   * @param string $label
   * @param mixed $value
   */
  public function addItem($value, $label){
    $option = new ListItem;
    $option->label = $label;
    $option->value = $value;
    $this->_items[$value] = $option;
    return $option;
  }
  
  /**
   * Get the items
   * @return array
   */
  public function getItems(){
    return $this->_items;
  }
  
  /**
   * Get the label for an item
   * @param integer $value
   * @return string
   */
  public function getLabelForValue($value){
    if(array_key_exists($value, $this->_items)){
      return $this->_items[$value]->label;
    }
    return '';
  }
}
/**
 * A single item for an element list
 * Radio, Checkbox, and select lists use these
 */
class ListItem extends HTML_Element {
  /**
   * HTML element attributes
   * @var string
   */
  public $disabled;
  public $value;
  
  /**
   * The label for this option
   * @var string
   */
  public $label;
  
  
  public function __construct(){
    parent::__construct();
    $this->_attributes['disabled'] = 'disabled';
    $this->_attributes['value'] = 'value';
  }
}
?>