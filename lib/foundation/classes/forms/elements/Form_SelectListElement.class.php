<?php
/**
 * A Select List Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_SelectListElement extends Form_ListElement{
  /**
   * HTML element attributes
   * @var string
   */
  public $multiple;

  /**
   * Constructor
   */
  public function __construct($field){
    parent::__construct($field);
    $this->attributes['multiple'] = 'multiple';
  }
}
?>