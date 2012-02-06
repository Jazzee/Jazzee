<?php
namespace Jazzee\Element;
/**
 * EmailAddress Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class EmailAddress extends TextInput {
  const PAGEBUILDER_SCRIPT = 'resource/scripts/element_types/JazzeeElementEmailAddress.js';
  public function addToField(\Foundation\Form\Field $field){
    $element = parent::addToField($field);
    
    $validator = new \Foundation\Form\Validator\EmailAddress($element, true);
    $element->addValidator($validator);
    
    return $element;
  }
}
?>