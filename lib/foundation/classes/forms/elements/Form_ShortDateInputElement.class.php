<?php
/**
 * A Short Date Element
 * Just the month and year
 * @author Jon Johnson <jon.johnson@ucsf.edu>f
 * @package foundation
 * @subpackage forms
 */
class Form_ShortDateInputElement extends Form_InputElement{
  
  /**
   * Transform ShortDate input into a valid date
   * @see Form_Element::validate()
   */
  public function validate(FormInput $input){
    $month = "{$this->name}-month";
    $year = "{$this->name}-year";
    if(!empty($input->{$month}) and !empty($input->{$year})){
      //create a date using the first day of the month
      $input->{$this->name} = trim($input->$year) . "-{$input->$month}-1";
    } else if(!is_null($input->{$this->name})){
      $arr = split(' ', $input->{$this->name});
      $input->{$this->name} = "{$arr[0]} 1 {$arr[1]}";
    }
    return parent::validate($input);
  }
}
?>