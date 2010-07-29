<?php
/**
 * Apply one of PHPs built in input sanitizers
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_PHPSanitizeFilter extends Form_Filter{
  public function filter($value){
    $options = null;
    if(is_int($this->ruleSet)){
      $type = $this->ruleSet;
    } else if(is_array($this->ruleSet)){
      $type = array_shift($this->ruleSet);
      if(count($this->ruleSet) > 0){
        $options = array_shift($this->ruleSet);
      }
    } else {
      throw new Foundation_Error("Invalid ruleset provided to PHPSanitizeFilter");
    }
    return filter_var($value, $type, $options);
  }
}
?>
