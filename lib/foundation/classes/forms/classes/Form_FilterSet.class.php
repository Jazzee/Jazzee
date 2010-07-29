<?php
/**
 * A set of Form_Filters
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_FilterSet extends Form_Set{   
  /**
   * Add a filter to the set
   * @param Form_Filter $f
   */
  public function addFilter(Form_Filter $f){
    $this->add($f);
  }
   
  /**
   * Loop filters and modify the input
   * @param mixed $value the user input
   */
  public function filter($value){
    while($f = $this->next()){
      $value = $f->filter($value);
    }
    return $value;
  }
}
?>
