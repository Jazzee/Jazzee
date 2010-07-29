<?php
/**
 * A set of Form_Validators
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_ValidatorSet extends Form_Set{
  /**
   * Errors which have been encounered during validation
   * @var array
   */
  private $_errors;
  
  /**
   * Has there been an error
   * @var bool
   */
  private $_hasError;
  
  /**
   * Constructor
   */
   public function __construct(){
     $this->_errors = array();
     $this->_hasError = false;
     parent::__construct();
   }
   
   /**
    * Add a validator to the set
    * @param Form_Validator $v 
    */
   public function addValidator(Form_Validator $v){
     $this->add($v);
   }
   
   /**
    * Register an error
    * @param Form_ValidatorError $e 
    */
   public function addError(Form_ValidationError $e){
     $this->_hasError = true;
     $this->_errors[] = $e;
   }
   
   /**
    * Get all of the errors
    */
   public function getErrors(){
     $arr = array();
     foreach($this->_errors as $e){
       $arr[] = $e->getMessage();
     }
     return $arr;
   }
   
   /**
    * Check to see if there are any errors
    */
   public function hasError(){
     return $this->_hasError;
   }
   
   /**
    * Loop validators and validate
    * @param FormInput $input
    */
   public function validate(FormInput $input){
     while($v = $this->next()){
       $v->validate($input);
     }
   }
   
   /**
    * Loop validators and pre-render
    * Allows validators to add attributes to an element
    */
   public function preRender(){
     while($v = $this->next()){
       $v->preRender();
     }
   }
}
?>
