<?php
/**
 * Convert the value to all lowercase
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_LowercaseFilter extends Form_Filter{
  public function filter($value){
    return strtolower($value);
  }
}
?>
