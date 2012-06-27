<?php

namespace Jazzee\Element;

/**
 * Phonenumber Element
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Phonenumber extends TextInput
{

  const PAGEBUILDER_SCRIPT = 'resource/scripts/element_types/JazzeeElementPhonenumber.js';

  public function addToField(\Foundation\Form\Field $field)
  {
    $element = $field->newElement('TextInput', 'el' . $this->_element->getId());
    $element->setLabel($this->_element->getTitle());
    $element->setInstructions($this->_element->getInstructions());
    $element->setFormat($this->_element->getFormat());
    $element->setDefaultValue($this->_element->getDefaultValue());
    if ($this->_element->isRequired()) {
      $validator = new \Foundation\Form\Validator\NotEmpty($element);
      $element->addValidator($validator);
    }
    $validator = new \Foundation\Form\Validator\Phonenumber($element);
    $element->addValidator($validator);

    $filter = new \Foundation\Form\Filter\Phonenumber($element);
    $element->addFilter($filter);

    return $element;
  }

}