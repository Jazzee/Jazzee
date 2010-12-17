<?php
/**
 * Create Dynamic Forms
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form extends HTML_Element{
  /**
   * HTML element attributes
   * @var string
   */
  public $action;
  public $accept_charset = 'utf8';
  public $enctype;
  public $method = 'post';
  public $name;
  
  /**
   * The form fields
   * @var array 
   */
  protected $fields = array();
  
  /**
   * Every Element
   * @var array
   */
  public $elements = array();
  
  /**
   * The hidden field
   * @var Form_Field
   */
  protected $hidden;
  
  /**
   * The button field
   * @var Form_Field
   */
  protected $buttons;
  
  /**
   * Constructor
   * Create the special hidden and button fields
   */
  public function __construct(){
    parent::__construct();
    $this->attributes['action'] = 'action';
    $this->attributes['enctype'] = 'enctype';
    $this->attributes['method'] = 'method';
    $this->attributes['accept_charset'] = 'accept-charset';
    $this->attributes['name'] = 'name';
    
    $this->hidden = new Form_Field($this);
    $this->hidden->class = 'hidden';
    $this->buttons = new Form_Field($this);
    $this->buttons->class = 'buttons';
  }
  
  /**
   * Create a new form field
   * @param array $attributes
   * 
   */
  public function newField($attributes = array()){
    $field = new Form_Field($this);
    foreach($attributes as $key=>$value){
      $field->$key = $value;
    }
    $this->fields[] = $field;
    return $field;
  }
  
  /**
   * Get the fields
   * @return array
   */
  public function getFields(){
    $fields = $this->fields;
    $fields[] = $this->buttons;
    $fields[] = $this->hidden;
    return $fields;
  }
  
  /**
   * Create hidden element
   * @param string $id 
   * @param string $value
   */
  public function newHiddenElement($name, $value){
    $e = $this->hidden->newElement('HiddenInput', $name);
    $e->value = $value;
    $e->defaultValue = $value;
    return $e; 
  }
  
  /**
   * Create button element
   * @param string $type
   * @param string $title
   */
  public function newButton($type, $value){
    $e = $this->buttons->newElement('ButtonInput',$type);
    $e->type = $type;
    $e->value = $value;
    $e->defaultValue = $value;
    return $e; 
  }
  
  /**
   * Process form input
   * If there is no input or a validation error then return false
   * @param array $arr
   * @return true|false if errors
   */
  public function processInput($arr){
    if(!empty($arr)){
      $input = new FormInput($arr);
      if($this->validate($input)){
        return $this->filter($input);
      }
      $this->setElementValues($input);
    }
    return false;
  }
  
  /**
   * Set Form_Element::value to the user input
   * @param FormInput $input
   */
  public function setElementValues(FormInput $input){
    foreach($this->elements as $element){
      $element->value = $input->{$element->name};
    }
  }
  
  /**
   * Validate user input
   * @param FormInput $input
   * @return bool true on success false on failure
   */
  public function validate(FormInput $input){
    $return = true;
    foreach($this->elements as $element){
      if(!$element->validate($input)) $return = false;
    }
    return $return;
  }
  
  /**
   * Filter user input
   * @param FormInput $input
   * @return FormInput
   */
  public function filter(FormInput $input){
    foreach($this->elements as $element){
      $input->{$element->name} = $element->filter($input);
    }
    return $input;
  }
  
  /**
   * Set all the elements to their default value
   */
  public function applyDefaultValues(){
    foreach($this->elements as $element){
      $element->value = $element->defaultValue;
    }
  }
  
  /**
   * Restet/Clear the form object
   * Usefull when the object needs to stick around, but the form is different
   */
  public function reset(){
    $this->hidden = new Form_Field($this);
    $this->hidden->class = 'hidden';
    $this->buttons = new Form_Field($this);
    $this->buttons->class = 'buttons';
    $this->fields = array();
    $this->elements = array();
  }
}
?>