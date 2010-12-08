<?php
/**
 * Encrypt the value using the PKI class
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_EncryptFilter extends Form_Filter{
  
  /**
   * Constructor
   * Ensure a valid PKI object is passed
   * @param Form_Element $e
   * @param PKI $pki
   */
  public function  __construct($e, PKI $pki){
    parent::__construct($e, $pki);
  }
  
  public function filter($value){
    return $this->ruleSet->encrypt($value);
  }
}
?>
