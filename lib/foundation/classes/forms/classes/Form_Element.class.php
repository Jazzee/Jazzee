<?php
/**
 * Form elements are contained in fields
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
abstract class Form_Element extends HTML_Element{
  /**
   * HTML element attributes
   * @var string
   */
  public $name;
  protected $value;
  public $accesskey;
  public $tabindex;
  
  /**
   * The label for this element
   * @var string 
   */
  public $label;
  
  /**
   * The format string
   * @var string
   */
   public $format;
   
   /**
    * The Instructions string
    * @var string
    */
   public $instructions;
    
  /**
   * The default value
   * @var string
   */
  public $defaultValue;
 
  /**
   * Is this element required
   */
  public $required = false;
  
  /**
   * Holds a reference to the field which contains this element
   * @var Field
   */
  public $field;
  
  /**
   * Holds all of the validators which must be run
   * @var Form_ValidatorSet
   */
  protected $_validatorSet;
  
  /**
   * Holds all of the filters which must be run
   * @var Form_FilterSet
   */
  protected $_filterSet;
  
  /**
   * Any user level messages to output
   * @var array
   */
  public $messages = array();
  
  /**
   * Constructor
   * @param Form_Field $field the field that contains this element
   */
  public function __construct(Form_Field $field){
    $this->field = $field;
    parent::__construct();
    
    $this->_attributes['name'] = 'name';
    $this->_attributes['value'] = 'value';
    $this->_attributes['accesskey'] = 'accesskey';
    $this->_attributes['tabindex'] = 'tabindex';
    
    $this->_validatorSet = new Form_ValidatorSet;
    $this->_filterSet = new Form_FilterSet;
  }
  
  /**
   * Set the value
   * @param $value string|array
   */
  public function setValue($value){
    $this->value = $value;
  }
  
  /**
   * Get the value for an element
   * @return string
   */
  public function getValue(){
    return $this->value;
  }
  
  /**
   * Add a message to the element
   * @param string $text
   */
  public function addMessage($text){
    $this->messages[] = $text;
  }
  
  /**
   *Add a validator to the que 
   *@param string $name the name of the validator class
   *@param mixed $ruleSet what we use to do validation
   */
  public function addValidator($name, $ruleSet = NULL){
    $class = "Form_{$name}Validator";
    if(!class_exists($class)){
      throw new Foundation_Exception("{$name} validator does not exist");
    }
    $validator = new $class($this, $ruleSet, $this->_validatorSet);
    $this->_validatorSet->addValidator($validator);
    return $validator;
  }
  
  /**
   *Add a filter to the que 
   *@param string $name the name of the filter class
   */
  public function addFilter($name, $ruleSet = NULL){
    $class = "Form_{$name}Filter";
    if(!class_exists($class)){
      throw new Foundation_Exception("{$name} filter does not exist");
    }
    $filter = new $class($this, $ruleSet);
    $this->_filterSet->addFilter($filter);
    return $filter;
  }

  /**
   * Run any validator preRender methods
   */
  public function preRender(){
    $this->_validatorSet->preRender();
  }
    
  /**
   * Validate user input
   * @param FormInput $input
   */
  public function validate(FormInput $input){
    $this->_validatorSet->validate($input);
    if($this->_validatorSet->hasError()){
      foreach($this->_validatorSet->getErrors() as $error){
        $this->addMessage($error);
      }
      return false;
    }
    return true;
  }
  
  /**
   * Filter user input
   * @param FormInput $input
   */
  public function filter(FormInput $input){
    if(is_null($input->{$this->name})) return null;
    return $this->_filterSet->filter($input->{$this->name});
  }
  
  /**
   * Get the type name of the element
   * @return string
   */
  public function elementType(){
    return preg_replace(array('/^Form_/','/Element$/'), '',get_class($this));
  }
  
}
?>