<?php
/**
 * Export application data to XML
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
class SetupExportApplicationController extends \Jazzee\AdminController {
  const MENU = 'Setup';
  const TITLE = 'Save Configuration';
  const PATH = 'setup/exportapplication';
  
  const ACTION_INDEX = 'Export Configuration';
  
  /**
   * If there is no application then create a new one to work with
   */
  protected function setUp(){
    parent::setUp();
    if(!$this->_application){
      $this->addMessage('notice', 'There is no data to export in this application.');
      $this->redirectPath('admin/welcome');
    }
  }
  
  /**
   * Setup the current application and cycle
   */
  public function actionIndex(){
    $this->setLayout('xml');
    $this->setVar('application', $this->_application);
  }
  
  /**
   * Create an xml node for a page
   * 
   * Calls itself recursivly to capture all children
   * @param DomDocument $xml
   * @param \Jazzee\Entity\Page $page
   */
  public function pageXml(DOMDocument $dom, $page){
    $pxml = $dom->createElement('page');
    $pxml->setAttribute('min', $page->getMin());
    $pxml->setAttribute('max', $page->getMax());
    $pxml->setAttribute('required', $page->isRequired());
    $pxml->setAttribute('answerStatusDisplay', $page->answerStatusDisplay());
    $pxml->setAttribute('instructions', $page->getInstructions());
    $pxml->setAttribute('leadingText', $page->getLeadingText());
    $pxml->setAttribute('trailingText', $page->getTrailingText());
    if($page instanceof \Jazzee\Entity\ApplicationPage){
      $pxml->setAttribute('weight', $page->getWeight());
      $page = $page->getPage();
    }
    $pxml->setAttribute('class', $page->getType()->getClass());
    
    $elements = $pxml->appendChild($dom->createElement('elements'));
    foreach($page->getElements() as $element){
      $exml = $dom->createElement('page');
      $exml->setAttribute('class', $element->getType()->getClass());
      $exml->setAttribute('fixedId', $element->getFixedId());
      $exml->setAttribute('weight', $element->getWeight());
      $exml->setAttribute('min', $element->getMin());
      $exml->setAttribute('max', $element->getMax());
      $exml->setAttribute('required', $element->isRequired());
      $exml->setAttribute('instructions', $element->getInstructions());
      $exml->setAttribute('format', $element->getFormat());
      $exml->setAttribute('defaultValue', $element->getDefaultValue());
      $elements->appendChild($exml);
    }
    $children = $pxml->appendChild($dom->createElement('children'));
    foreach($page->getChildren() as $child) $children->appendChild($this->pageXml($dom, $child));
    
    $variables = $pxml->appendChild($dom->createElement('variables'));
    foreach($page->getVariables() as $var){
      $variable = $dom->createElement('variable', $var->getValue());
      $variable->setAttribute('name', $var->getName());
      $variables->appendChild($variable);
    } 
    return $pxml;
  }
  
}