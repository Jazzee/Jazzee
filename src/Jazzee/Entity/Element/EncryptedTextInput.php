<?php
namespace Jazzee\Entity\Element;
/**
 * EncryptedTextInput Element
 * Filter the text input through PKI to get an encrypted value
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class EncryptedTextInput extends TextInput {
  /**
   * 
   * Foundation PKI class
   * @var \Foundation\PKI
   */
  private $_pki;
  
  /**
   * Constructor
   * Establish the Foundation PKI class
   * @param \Jazzee\Entity\Element $element
   */
  public function __construct(\Jazzee\Entity\Element $element){
    parent::__construct($element);
    $config = new \Jazzee\Configuration();
    if(!is_readable($config->getPublicKeyCertificatePath())){
      throw new \Jazzee\Exception('Unable to read public key for EncryptedInput: ' . $config->getPublicKeyCertificatePath());
    }
    $this->_pki = new \Foundation\PKI();
    $this->_pki->setPublicKey(file_get_contents($config->getPublicKeyCertificatePath()));
  }
  
  public function addToField(\Foundation\Form\Field $field){
    $element = parent::addToField($field);
    $filter = new \Foundation\Form\Filter\Encrypt($element, $this->_pki);
    $element->addFilter($filter);
  }
  
  public function getElementAnswers($input){
    $elementAnswers = array();
    if(!is_null($input)){
      $elementAnswer = new \Jazzee\Entity\ElementAnswer;
      $elementAnswer->setElement($this->_element);
      $elementAnswer->setPosition(0);
      $elementAnswer->setEText($input);
      $elementAnswers[] = $elementAnswer;
    }
    return $elementAnswers;
  }
  
  public function displayValue(\Jazzee\Entity\Answer $answer){
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if(isset($elementsAnswers[0])){
      return 'encrypted';
    }
    return null;
  }
  
  public function formValue(\Jazzee\Entity\Answer $answer){
    return null;
  }
  
  public function rawValue(\Jazzee\Entity\Answer $answer){
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if(isset($elementsAnswers[0])){
      return $elementsAnswers[0]->getEText();
    }
    return null;
  }
  
  public function pdfValue(\Jazzee\Entity\Answer $answer, \Jazzee\ApplicantPDF $pdf){
    return $this->displayValue($answer);
  }
}
?>