<?php

namespace Jazzee\Element;

/**
 * EmailAddress Element
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class EmailAddress extends TextInput
{

  const PAGEBUILDER_SCRIPT = 'resource/scripts/element_types/JazzeeElementEmailAddress.js';

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
    $validator = new \Foundation\Form\Validator\EmailAddress($element, true);
    $element->addValidator($validator);

    return $element;
  }

}