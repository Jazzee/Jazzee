<?php
namespace Jazzee\Entity\Element;
/**
 * EmailAddress Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class EmailAddress extends TextInput {
  public function addToField(\Foundation\Form\Field $field){
    $element = parent::addToField($field);
    
    $validator = new \Foundation\Form\Validator\EmailAddress($element, true);
    $element->addValidator($validator);
    
    return $element;
  }
}
?>