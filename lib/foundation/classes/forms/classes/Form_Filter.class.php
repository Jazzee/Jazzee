<?php
/**
 * Abstract class to define form element filters
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
abstract class Form_Filter{
  /**
   * Holds the element we are filtering
   * @var Form_Element
   */
  protected $e; 
  
  /**
   * Holds the rule set for processing
   * @var mixed
   */
  protected $rulesSet;
  
  /**
   * Constructor
   * @param Form_Element $e the element we are validating
   */
  public function  __construct(Form_Element $e, $ruleSet = null){
    $this->e = $e;
    $this->ruleSet = $ruleSet;
  }
  
  /**
   * Filter the input
   * @param mixed $value
   * @return mixed @value
   */
  abstract public function filter($value);
}
?>