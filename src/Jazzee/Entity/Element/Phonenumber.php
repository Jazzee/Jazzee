<?php
namespace Jazzee\Entity\Element;
/**
 * Phonenumber Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class Phonenumber extends TextInput {
  public function addToField(\Foundation\Form\Field $field){
    $element = parent::addToField($field);
    
    $validator = new \Foundation\Form\Validator\Phonenumber($element);
    $element->addValidator($validator);
    
    $filter = new \Foundation\Form\Filter\Phonenumber($element);
    $element->addFilter($filter);
    
    return $element;
  }
}
?>