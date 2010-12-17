<?php
/**
 * A Abstract Input Element
 * Passwords, text boxes, dates all descend from here
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
abstract class Form_InputElement extends Form_Element{
  /**
   * HTML element attributes
   * @var string
   */
  public $type = 'text';
  public $maxlength;
  public $disabled;

  /**
   * Constructor
   */
  public function __construct($field){
    parent::__construct($field);
    $this->attributes['disabled'] = 'disabled';
    $this->attributes['type'] = 'type';
    $this->attributes['maxlength'] = 'maxlength';
  }
}
?>