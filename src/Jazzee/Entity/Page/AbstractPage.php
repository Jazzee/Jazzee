<?php
namespace Jazzee\Entity\Page;
/**
 * AbstractPage
 */
abstract class AbstractPage implements \Jazzee\Page {
  const ERROR_MESSAGE = 'There was a problem saving your data on this page.  Please correct the errors below and retry your request.';
 /**
  * The ApplicationPage Entity
  * @var \Jazzee\Entity\ApplicationPage
  */
  protected $_applicationPage;
    
  /**
   * Our controller
   * @var \Jazzee\Controller
   */
  protected $_controller;
  
  /**
   * The Applicant
   * @var \Jazzee\Entity\Applicant
   */
  protected $_applicant;
  
  /**
   * Our form
   * @var \Foundation\Form
   */
  protected $_form;
  
 /**
  * Contructor
  * 
  * @param \Jazzee\Entity\ApplicationPage $applicationPage
  */
  public function __construct(\Jazzee\Entity\ApplicationPage $applicationPage){
    $this->_applicationPage = $applicationPage;
  }
  
  /**
   * 
   * @see Jazzee.Page::setController()
   */
  public function setController(\Jazzee\Controller $controller){
    $this->_controller = $controller;
  }
  
  /**
   * 
   * @see Jazzee.Page::setApplicant()
   */
  public function setApplicant(\Jazzee\Entity\Applicant $applicant){
    $this->_applicant = $applicant;
  }
  
  /**
   * @see Jazzee.Page::getForm()
   */
  public function getForm(){
    if(is_null($this->_form)) $this->_form = $this->makeForm();
    return $this->_form;
  }
  
  /**
   * Make the form for the page
   * @return \Foundation\Form or false if no form
   */
  abstract protected function makeForm();
  
  /**
   * 
   * @see Jazzee.Page::validateInput()
   */
  public function validateInput($arr){
    if($input = $this->getForm()->processInput($arr)){
      return $input;
    }
    $this->_controller->addMessage('error', self::ERROR_MESSAGE);
    return false;
  }
  
  /**
   * Most pages don't require any setup
   * @see Jazzee.Page::setupNewPage()
   */
  public function setupNewPage(){
    return;
  }
  
/**
   * (non-PHPdoc)
   * @see Jazzee.Page::showReviewPage()
   */
  public function showReviewPage(){
    return true;
  }
  
  /**
   * Convert an answer to an xml element
   * @param \DomDocument $dom
   * @param \Jazzee\Entity\Answer $answer
   * @return \DomElement
   */
  protected function xmlAnswer(\DomDocument $dom, \Jazzee\Entity\Answer $answer){
    $answerXml = $dom->createElement('answer');
    $answerXml->setAttribute('answerId', $answer->getId());
    $answerXml->setAttribute('updatedAt', $answer->getUpdatedAt()->format('c'));
    $answerXml->setAttribute('pageStatus', $answer->getPageStatus());
    foreach($answer->getPage()->getElements() as $element){
      $eXml = $dom->createElement('element');
      $eXml->setAttribute('elementId', $element->getId());
      $eXml->setAttribute('title', htmlentities($element->getTitle(),ENT_COMPAT,'utf-8'));
      $eXml->setAttribute('type', htmlentities($element->getType()->getClass(),ENT_COMPAT,'utf-8'));
      if($value = $element->getJazzeeElement()->rawValue($answer)) $eXml->appendChild($dom->createCDATASection($value));
      $answerXml->appendChild($eXml);
    }
    $attachment = $dom->createElement('attachment');
    if($answer->getAttachment()) $attachment->appendChild($dom->createCDATASection(base64_encode($answer->getAttachment()->getAttachment())));
    $answerXml->appendChild($attachment);
    
    $children = $dom->createElement('children');
    foreach($answer->getChildren() as $child){
      $children->appendChild($this->xmlAnswer($dom, $child));
    }
    $answerXml->appendChild($children);
    return $answerXml;
  }
}

?>