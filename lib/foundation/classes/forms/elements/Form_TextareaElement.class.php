<?php
/**
 * A Textarea Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_TextareaElement extends Form_Element{
  /**
   * HTML element attributes
   * @var string
   */
  public $cols;
  public $rows;
  public $disabled;
  public $readonly;

  /**
   * Constructor
   */
  public function __construct($field){
    parent::__construct($field);
    $this->_attributes['cols'] = 'cols';
    $this->_attributes['rows'] = 'rows';
    $this->_attributes['disabled'] = 'disabled';
    $this->_attributes['readonly'] = 'readonly';
  }
}
?>