<?php

namespace Jazzee\Element;

/**
 * EncryptedTextInput Element
 * Filter the text input through PKI to get an encrypted value
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class EncryptedTextInput extends TextInput
{

  const PAGEBUILDER_SCRIPT = 'resource/scripts/element_types/JazzeeElementEncryptedTextInput.js';

  /**
   *
   * Foundation PKI class
   * @var \Foundation\PKI
   */
  private $_pki;

  protected function setupPki()
  {
    $config = $this->_controller->getConfig();
    if (!is_readable($config->getPublicKeyCertificatePath())) {
      throw new \Jazzee\Exception('Unable to read public key for EncryptedInput: ' . $config->getPublicKeyCertificatePath());
    }
    $this->_pki = new \Foundation\PKI();
    $this->_pki->setPublicKey(file_get_contents($config->getPublicKeyCertificatePath()));
  }

  public function addToField(\Foundation\Form\Field $field)
  {
    $this->setupPki();
    $element = parent::addToField($field);
    $filter = new \Foundation\Form\Filter\Encrypt($element, $this->_pki);
    $element->addFilter($filter);

    return $element;
  }

  public function getElementAnswers($input)
  {
    $elementAnswers = array();
    if (!is_null($input)) {
      $elementAnswer = new \Jazzee\Entity\ElementAnswer;
      $elementAnswer->setElement($this->_element);
      $elementAnswer->setPosition(0);
      $elementAnswer->setEText($input);
      $elementAnswers[] = $elementAnswer;
    }

    return $elementAnswers;
  }

  public function displayValue(\Jazzee\Entity\Answer $answer)
  {
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementsAnswers[0])) {
      return 'encrypted';
    }

    return null;
  }
  
  protected function arrayDisplayValue(array $values)
  {
    if (isset($values[0])) {
      return 'encrypted';
    }

    return '';
  }

  public function formValue(\Jazzee\Entity\Answer $answer)
  {
    return null;
  }

  public function rawValue(\Jazzee\Entity\Answer $answer)
  {
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementsAnswers[0])) {
      return $elementsAnswers[0]->getEText();
    }

    return null;
  }

  public function pdfValue(\Jazzee\Entity\Answer $answer, \Jazzee\ApplicantPDF $pdf)
  {
    return $this->displayValue($answer);
  }

}