<?php
/**
 * Form elements are contained in fields
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_Field extends HTML_Element{
  /**
   * Any field level instructions
   * @var string
   */
  public $instructions = '';
  
  /**
   * The legend for the field
   * @var string
   */
  public $legend = '';
  
  /**
   * Holds the field elements
   * @var array
   */
  protected $elements = array();
  
  /**
   * Holds a reference to the form
   * @var Form
   */
  public $form;
  
  /**
   * Constructor
   * @param Form $form the form that contains this field
   */
  public function __construct(Form $form){
    parent::__construct();
    $this->form = $form;
  }
  
  /**
   * Create a new form element
   * @param string $type what kind of element to create
   * @param string $name the unique name of the element
   * @param array $attributes any attributes to set
   * @param array $validators multidemensional array of validators
   * @param array $filters multidemensional array of filters
   * 
   */
  public function newElement($type, $name, $attributes = array(), $validators=array(), $filters=array()){
    $class = "Form_{$type}Element";
    if(!class_exists($class)){
      throw new Foundation_Exception("{$class} does not exist");
    }
    $element = new $class($this);
    $element->class = $type;
    $element->name = $name;
    foreach($attributes as $key=>$value){
      $element->$key = $value;
    }
    foreach($validators as $validator => $ruleset){
      $element->addValidator($validator, $ruleset);
    }
    foreach($filters as $filter => $ruleset){
      $element->addFilter($filter, $ruleset);
    }
    $this->form->elements[$name] = $element;
    $this->elements[$name] = $element;
    return $element;
  }
  
  /**
   * Get the Elements
   * @return array
   */
  public function getElements(){
    return $this->elements;
  }
}
?>