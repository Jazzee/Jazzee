<?php
/**
 * User input from a form
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class FormInput{
  /**
   * Read/Write store of user input
   * Will be acted on by Filters and Proccesed by Validators
   * @var array
   */
  protected $input = array();
  
  /**
   * Read Only raw original input
   * @var array
   */
  protected $rawInput = array();
  
  
  /**
   * Constructor
   * Take the user input and fill the containers
   * @param array $input
   */
  public function __construct($input){
    $this->rawInput = $input;
    foreach($input as $key => $value){
      if(get_magic_quotes_gpc())
        $value = stripslashes($value);
      if($value === '') //convert empty strings to null values
        $value = null;
      $this->$key = $value;
    }
  }
  
  /**
   * Store data
   * @param string $name the name of the data
   * @param mixed $value the value to store
   */
  public function __set($name, $value){
   $this->input[$name] = $value;
  }
  
  /**
   * Retrieve data
   * @param string $name the name of the data
   * @return mixed input data if it is set null if it isn't
   */
  public function __get($name){
    if(array_key_exists($name, $this->input)){
      return $this->input[$name];
    }
    return null;
  }
  
  /**
   * Check if a property is set
   * @param string $name
   */
  public function __isset($name){
    return isset($this->input[$name]);
  }
  
  /**
   * Unset a property
   * @param string $name
   */
  public function __unset($name){
    unset($this->input[$name]);
  }
  
}
?>