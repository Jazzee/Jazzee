<?php

namespace Jazzee\Element;

/**
 * USSocialSecutiryNumber Element
 * Validate an SSN
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class USSocialSecurityNumber extends EncryptedTextInput
{

  const PAGEBUILDER_SCRIPT = 'resource/scripts/element_types/JazzeeElementUSSocialSecurityNumber.js';

  public function addToField(\Foundation\Form\Field $field)
  {
    $element = parent::addToField($field);
    $validator = new \Foundation\Form\Validator\Regex($element, '/^(?!000)([0-6]\d{2}|7([0-6]\d|7[012]))([ -]?)(?!00)\d\d([ -]?)(?!0000)\d{4}$/');
    $validator->setErrorMessage('This is not a valid US Social Secutiry Number');
    $element->addValidator($validator);
    //check if every digit is the same
    $validator = new \Foundation\Form\Validator\Regex($element, '/^(\d)(?!\1+$)\d*$/');
    $validator->setErrorMessage('This is not a valid US Social Secutiry Number');
    $element->addValidator($validator);
    //check for specific problem cases
    $problems = array(
      '123456789',
      '111223333',
      '111223456'
    );
    $validator = new \Foundation\Form\Validator\Regex($element, '/^((?!.*(' .implode('|', $problems) . ')).*)$/');
    $validator->setErrorMessage('This is not a valid US Social Secutiry Number bad');
    $element->addValidator($validator);
    $element->prependFilter(new \Foundation\Form\Filter\Replace($element, array('pattern'=>'/[^0-9]/', 'replace'=>'')));

    return $element;
  }

}