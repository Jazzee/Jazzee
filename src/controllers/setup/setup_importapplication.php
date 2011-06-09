<?php
/**
 * Import application data from XML
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
class SetupImportApplicationController extends \Jazzee\AdminController {
  const MENU = 'Setup';
  const TITLE = 'Import Configuration';
  const PATH = 'setup/importapplication';
  
  const ACTION_INDEX = 'Import Configuration';
  
  /**
   * Setup the current application and cycle
   */
  public function actionIndex(){
    $form = new \Foundation\Form();
    $form->setAction($this->path("admin/setup/importapplication"));
    $field = $form->newField();
    $field->setLegend('Import Application');
    
    $element = $field->newElement('FileInput','file');
    $element->setLabel('XML Configuration');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Blob($element));
    
    

    $form->newButton('submit', 'Import');
    
    if($input = $form->processInput($this->post)){
      $xml = new \DOMDocument();
      $xml->loadXML($input->get('file'));
      $root = $xml->getElementsByTagName('application')->item(0);
      
      if(!$this->_application){
        $this->_application = new \Jazzee\Entity\Application();
        $this->_application->setProgram($this->_program);
        $this->_application->setCycle($this->_cycle);
      }
      if($this->_application->isPublished()){
        $this->addMessage('error', 'This application is already published.  No changes can be made.');
        $this->redirectPath('admin/setup/importapplication');
      }
      if(count($this->_application->getPages())){
        $this->addMessage('error', 'This application already has pages.  You cannot import a configuration for an application with pages.');
        $this->redirectPath('admin/setup/importapplication');
      }
      foreach($root->getElementsByTagName('preferences')->item(0)->getElementsByTagName('*') as $element){
        $method = 'set' . ucfirst($element->tagName);
        $this->_application->$method($element->nodeValue);
      }
      for ($node = $root->getElementsByTagName('pages')->item(0)->firstChild; $node !== null; $node = $node->nextSibling) {
        if ($node instanceof DOMElement && $node->tagName == "page") {
            $page = $this->addPageFromXml($node);
            $applicationPage = new \Jazzee\Entity\ApplicationPage();
            $applicationPage->setApplication($this->_application);
            $applicationPage->setPage($page);
            $applicationPage->setTitle($node->getAttribute('title'));
            $applicationPage->setMin($node->getAttribute('min'));
            $applicationPage->setMax($node->getAttribute('max'));
            if($node->getAttribute('required'))$applicationPage->required(); else $applicationPage->optional();
            if($node->getAttribute('answerStatusDisplay'))$applicationPage->showAnswerStatus(); else $applicationPage->hideAnswerStatus();
            $applicationPage->setInstructions($node->getAttribute('instructions'));
            $applicationPage->setLeadingText($node->getAttribute('leadingText'));
            $applicationPage->setTrailingText($node->getAttribute('trailingText'));
            $applicationPage->setWeight($node->getAttribute('weight'));
            $this->_em->persist($applicationPage);
        }
      }
      $this->_em->persist($this->_application);
      $this->addMessage('success', 'Application imported successfully');
    }
    
    $this->setVar('form', $form);
  }
  
  protected function addPageFromXml(DOMElement $xml){
    if($xml->hasAttribute('globalPageUuid')){
      $page = $this->_em->getRepository('\Jazzee\Entity\Page')->findOneBy(array('isGlobal'=>true,'uuid'=>$xml->getAttribute('globalPageUuid')));
      if(!$page){
        $this->addMessage('error', $xml->getAttribute('title') . ' page in import references global page with uuid '. $xml->getAttribute('globalPageUuid') . ' but this page does not exist.  You need to import it before importing this application.');
        $this->redirectPath('admin/setup/importapplication');
      }
    } else {
      $page = new \Jazzee\Entity\Page();
      $page->setType($this->_em->getRepository('\Jazzee\Entity\PageType')->findOneBy(array('class'=>$xml->getAttribute('class'))));
      $page->setTitle($xml->getAttribute('title'));
      $page->setMin($xml->getAttribute('min'));
      $page->setMax($xml->getAttribute('max'));
      if($xml->getAttribute('required'))$page->required(); else $page->optional();
      if($xml->getAttribute('answerStatusDisplay'))$page->showAnswerStatus(); else $page->hideAnswerStatus();
      $page->setInstructions($xml->getAttribute('instructions'));
      $page->setLeadingText($xml->getAttribute('leadingText'));
      $page->setTrailingText($xml->getAttribute('trailingText'));
      $page->notGlobal();
      $this->_em->persist($page);
      for ($node = $xml->getElementsByTagName('elements')->item(0)->firstChild; $node !== null; $node = $node->nextSibling) {
        if ($node instanceof DOMElement && $node->tagName == "element") {
          $element = new \Jazzee\Entity\Element;
          $element->setType($this->_em->getRepository('\Jazzee\Entity\ElementType')->findOneBy(array('class'=>$node->getAttribute('class'))));
          if($node->getAttribute('fixedId')) $element->setFixedId($node->getAttribute('fixedId'));
          $element->setTitle($node->getAttribute('title'));
          $element->setMin($node->getAttribute('min'));
          $element->setMax($node->getAttribute('max'));
          if($node->getAttribute('required'))$element->required(); else $element->optional();
          $element->setInstructions($node->getAttribute('instructions'));
          $element->setFormat($node->getAttribute('format'));
          $element->setWeight($node->getAttribute('weight'));
          $page->addElement($element);
          foreach($node->getElementsByTagName('listitems')->item(0)->getElementsByTagName('item') as $listElement){
            $listItem = new \Jazzee\Entity\ElementListItem();
            $listItem->setValue($listElement->nodeValue);
            $listItem->setWeight($listElement->getAttribute('weight'));
            if($listElement->getAttribute('active')) $listItem->activate(); else $listItem->deactivate();
            $element->addItem($listItem);
            $this->_em->persist($listItem);
          }
          $this->_em->persist($element);
        }
      }
      
      for ($node = $xml->getElementsByTagName('variables')->item(0)->firstChild; $node !== null; $node = $node->nextSibling) {
        if ($node instanceof DOMElement && $node->tagName == "variable") {
          $var = $page->setVar($node->getAttribute('name'), $node->nodeValue);
          $this->_em->persist($var);
        }
      }
      for ($node = $xml->getElementsByTagName('children')->item(0)->firstChild; $node !== null; $node = $node->nextSibling) {
        if ($node instanceof DOMElement && $node->tagName == "page") {
//          $childPage = $this->addPageFromXml($node);
//          $page->addChild($childPage);
        }
      }
    }
    return $page;
  }
  
  
}