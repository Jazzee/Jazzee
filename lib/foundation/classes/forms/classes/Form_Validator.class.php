<?php
/**
 * Abstract class to define form element validators
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
abstract class Form_Validator{
  /**
   * Holds the element we are validating
   * @var Form_Element
   */
  protected $_e; 
  
  /**
   * Holds the set we belong to
   * @var Form_ValidatorSet
   */
  protected $_s;
  
  /**
   * Holds the rule set for processing
   * @var mixed
   */
  protected $_rulesSet;
  
  /**
   * @param Form_Element $e the element we are validating
   * @param mixed $ruleSet rules set to use might be an array of rules or just a value to match
   * @param Form_ValidatorSet $set the set we belogn to
   */
  public function  __construct(Form_Element $e, $ruleSet, Form_ValidatorSet $set){
    $this->_e = $e;
    $this->_ruleSet = $ruleSet;
    $this->_s = $set;
  }
  
  /**
   * Add an error
   * @param string $text error text
   */
  protected function addError($text){
    $this->_s->addError(new Form_ValidationError($text));
  }
  
  /**
   * Do this before rendering the element
   */
  public function preRender(){}
  
  /**
   * Abstract Validator
   * @param FormInput $input
   */
  abstract public function validate(FormInput $input);
}
?>