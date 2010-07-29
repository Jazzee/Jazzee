<?php
/**
 * The Abstract Application Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
abstract class ApplyElement {
 /**
  * The Element model
  * @var Element $element
  */
 protected $element;
 
 /**
  * The input
  * @param mixed $value
  */
 protected $value;
 
 /**
  * Contructor
  */
  public function __construct(Element $element){
    $this->element = $element;
  }
  
  /**
   * Passthrough unset get request to the $element object
   * @param $name
   */
  public function __get($name){
    $method = "get{$name}";
    if(method_exists($this, $method)){
      return $this->$method();
    }
    return $this->element->$name;
  }
  
  /**
   * Add a form element to the supplied field
   * @param Form_Field $field
   * @return Form_Element
   */
  abstract public function addToField(Form_Field $field);
  
  /**
   * Store input as an ElementAnswer
   * @param mixed $input
   */
  abstract public function setValueFromInput($input);
  
  /**
   * Set the value from an ApplyAnswer Object
   * @param array $answers
   */
  abstract public function setValueFromAnswer($answers);
  
  /**
   * Get an array of ElementAnswers from the $value
   * @return array
   */
  abstract public function getAnswers();
  
  /**
   * Get the value of the element formated for display
   * @return string
   */
  abstract public function displayValue();
  
  /**
   * Get the value of the element that Form_Element will accept
   * @return mixed
   */
  abstract public function formValue();
  
  /**
   * Does this element have list items
   * @return bool
   */
  abstract public function hasListItems();
  
  /**
   * Get the edit form for this element
   * @return Form
   */
  abstract public function getPropertiesForm();
  
  /**
   * Set the properties from the input
   * @param FormInput $input
   */
  abstract public function setProperties(FormInput $input);
  
}
?>