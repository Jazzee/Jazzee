<?php
/**
 * Convert value to a nice date
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_DateFormatFilter extends Form_Filter{
  public function filter($value){
    return date($this->ruleSet, strtotime($value));
  }
}
?>
