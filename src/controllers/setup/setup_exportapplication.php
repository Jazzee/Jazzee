<?php

/**
 * Export application data to XML
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class SetupExportApplicationController extends \Jazzee\AdminController
{

  const MENU = 'Setup';
  const TITLE = 'Save Configuration';
  const PATH = 'setup/exportapplication';
  const ACTION_INDEX = 'Export Configuration';

  /**
   * If there is no application then create a new one to work with
   */
  protected function setUp()
  {
    parent::setUp();
    if (!$this->_application) {
      $this->addMessage('notice', 'There is no data to export in this application.');
      $this->redirectPath('welcome');
    }
  }

  /**
   * Setup the current application and cycle
   */
  public function actionIndex()
  {
    $this->setLayout('xml');
    $this->setLayoutVar('filename', $this->_application->getProgram()->getShortName() . '-' . $this->_application->getCycle()->getName() . '.xml');
    $this->setVar('application', $this->_application);
  }

  /**
   * Create an xml node for a page
   *
   * Calls itself recursivly to capture all children
   * @param DomDocument $xml
   * @param \Jazzee\Entity\Page $page
   */
  public function pageXml(DOMDocument $dom, $page)
  {
    $pxml = $dom->createElement('page');
    $pxml->setAttribute('title', htmlentities($page->getTitle(), ENT_COMPAT, 'utf-8'));
    $pxml->setAttribute('min', $page->getMin());
    $pxml->setAttribute('max', $page->getMax());
    $pxml->setAttribute('required', $page->isRequired());
    $pxml->setAttribute('answerStatusDisplay', $page->answerStatusDisplay());

    $pxml->appendChild($this->createCdataElement($dom, 'instructions', $page->getInstructions()));
    $pxml->appendChild($this->createCdataElement($dom, 'leadingText', $page->getLeadingText()));
    $pxml->appendChild($this->createCdataElement($dom, 'trailingText', $page->getTrailingText()));

    if ($page instanceof \Jazzee\Entity\ApplicationPage) {
      $pxml->setAttribute('weight', $page->getWeight());
      $pxml->setAttribute('kind', $page->getKind());
      $pxml->setAttribute('name', $page->getName());
      $page = $page->getPage();
      if ($page->isGlobal()) {
        $pxml->setAttribute('globalPageUuid', $page->getUuid());
      }
    }
    $pxml->setAttribute('class', $page->getType()->getClass());

    $elements = $pxml->appendChild($dom->createElement('elements'));
    foreach ($page->getElements() as $element) {
      $exml = $dom->createElement('element');
      $exml->setAttribute('title', $element->getTitle());
      $exml->setAttribute('name', $element->getName());
      $exml->setAttribute('class', $element->getType()->getClass());
      $exml->setAttribute('fixedId', $element->getFixedId());
      $exml->setAttribute('weight', $element->getWeight());
      $exml->setAttribute('min', $element->getMin());
      $exml->setAttribute('max', $element->getMax());
      $exml->setAttribute('required', $element->isRequired());
      $exml->setAttribute('instructions', htmlentities($element->getInstructions(), ENT_COMPAT, 'utf-8'));
      $exml->setAttribute('format', htmlentities($element->getFormat(), ENT_COMPAT, 'utf-8'));
      $exml->setAttribute('defaultValue', $element->getDefaultValue());
      $listItems = $exml->appendChild($dom->createElement('listitems'));
      foreach ($element->getListItems() as $item) {
        //only export active items
        if ($item->isActive()) {
          $ixml = $this->createCdataElement($dom, 'item', $item->getValue());
          $ixml->setAttribute('active', (integer) $item->isActive());
          $ixml->setAttribute('weight', $item->getWeight());
          $ixml->setAttribute('name', $item->getName());
          $listItems->appendChild($ixml);
          unset($ixml);
        }
      }
      $elements->appendChild($exml);
    }
    $children = $pxml->appendChild($dom->createElement('children'));
    foreach ($page->getChildren() as $child) {
      $children->appendChild($this->pageXml($dom, $child));
    }

    $variables = $pxml->appendChild($dom->createElement('variables'));
    foreach ($page->getVariables() as $var) {
      $variable = $dom->createElement('variable', (string) $var->getValue());
      $variable->setAttribute('name', $var->getName());
      $variables->appendChild($variable);
    }

    return $pxml;
  }

  /**
   * Create a CDATA section from a value
   * @param DOMDocument $dom
   * @param string $elementName
   * @param string $value
   * @return \DOMElement
   */
  public function createCdataElement(\DOMDocument $dom, $elementName, $value){
    $element = $dom->createElement($elementName);
    if($value){
      $element->appendChild($dom->createCDATASection($value));
    }
    return $element;
  }

}