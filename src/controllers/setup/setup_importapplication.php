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
  const REQUIRE_APPLICATION = false;
  
  /**
   * Setup the current application and cycle
   */
  public function actionIndex(){
    $form = new \Foundation\Form();
    $form->setAction($this->path("setup/importapplication"));
    $field = $form->newField();
    $field->setLegend('Import Application');
    
    $element = $field->newElement('FileInput','file');
    $element->setLabel('XML Configuration');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Blob($element));
    
    

    $form->newButton('submit', 'Import');
    
    if($input = $form->processInput($this->post)){
      $xml = simplexml_load_string($input->get('file'));
      if(!$this->_application){
        $this->_application = new \Jazzee\Entity\Application();
        $this->_application->setProgram($this->_program);
        $this->_application->setCycle($this->_cycle);
      }
      if($this->_application->isPublished()){
        $this->addMessage('error', 'This application is already published.  No changes can be made.');
        $this->redirectPath('setup/importapplication');
      }
      $pages = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findBy(array('application'=>$this->_application()->getId(), 'kind'=>\Jazzee\Entity\ApplicationPage::APPLICATION), array('weight'=> 'asc'));
    
      if(count($pages)){
        $this->addMessage('error', 'This application already has pages.  You cannot import a configuration for an application with pages.');
        $this->redirectPath('setup/importapplication');
      }
      $preferences = $xml->xpath('/response/application/preferences');
      foreach($preferences[0]->children() as $element){
        $method = 'set' . ucfirst($element->getName());
        $this->_application->$method((string)$element);
      }
      foreach($xml->xpath('/response/application/pages/page') as $element) {
        $attributes = $element->attributes();
        $page = $this->addPageFromXml($element);
        $applicationPage = new \Jazzee\Entity\ApplicationPage();
        $applicationPage->setApplication($this->_application);
        $applicationPage->setPage($page);
        $applicationPage->setKind((string)$attributes['kind']);
        $applicationPage->setTitle((string)$attributes['title']);
        $applicationPage->setMin((string)$attributes['min']);
        $applicationPage->setMax((string)$attributes['max']);
        if((string)$attributes['required'])$applicationPage->required(); else $applicationPage->optional();
        if((string)$attributes['answerStatusDisplay'])$applicationPage->showAnswerStatus(); else $applicationPage->hideAnswerStatus();
        $applicationPage->setInstructions((string)$attributes['instructions']);
        $applicationPage->setLeadingText((string)$attributes['leadingText']);
        $applicationPage->setTrailingText((string)$attributes['trailingText']);
        $applicationPage->setWeight((string)$attributes['weight']);
        $this->_em->persist($applicationPage);
      }
      $this->_em->persist($this->_application);
      $this->addMessage('success', 'Application imported successfully');
      unset($this->_store->AdminControllerGetNavigation);
    }
    
    $this->setVar('form', $form);
  }
  
  protected function addPageFromXml(SimpleXMLElement $xml){
    $attributes = $xml->attributes();
    if(!empty($attributes['globalPageUuid'])){
      $page = $this->_em->getRepository('\Jazzee\Entity\Page')->findOneBy(array('isGlobal'=>true,'uuid'=>$attributes['globalPageUuid']));
      if(!$page){
        $this->addMessage('error', $xml->getAttribute('title') . ' page in import references global page with uuid '. $xml->getAttribute('globalPageUuid') . ' but this page does not exist.  You need to import it before importing this application.');
        $this->redirectPath('setup/importapplication');
      }
    } else {
      $page = new \Jazzee\Entity\Page();
      $page->setType($this->_em->getRepository('\Jazzee\Entity\PageType')->findOneBy(array('class'=>(string)$attributes['class'])));
      $page->setTitle((string)$attributes['title']);
      $page->setMin((string)$attributes['min']);
      $page->setMax((string)$attributes['max']);
      if((string)$attributes['required'])$page->required(); else $page->optional();
      if((string)$attributes['answerStatusDisplay'])$page->showAnswerStatus(); else $page->hideAnswerStatus();
      $page->setInstructions((string)$attributes['instructions']);
      $page->setLeadingText((string)$attributes['leadingText']);
      $page->setTrailingText((string)$attributes['trailingText']);
      $page->notGlobal();
      $this->_em->persist($page);
      foreach($xml->xpath('elements/element') as $elementElement){
        $attributes = $elementElement->attributes();
        $element = new \Jazzee\Entity\Element;
        $element->setType($this->_em->getRepository('\Jazzee\Entity\ElementType')->findOneBy(array('class'=>(string)$attributes['class'])));
        if((string)$attributes['fixedId']) $element->setFixedId((string)$attributes['fixedId']);
        $element->setTitle((string)$attributes['title']);
        $element->setMin((string)$attributes['min']);
        $element->setMax((string)$attributes['max']);
        if((string)$attributes['required'])$element->required(); else $element->optional();
        $element->setInstructions((string)$attributes['instructions']);
        $element->setFormat((string)$attributes['format']);
        $element->setWeight((string)$attributes['weight']);
        $page->addElement($element);
        foreach($elementElement->xpath('listitems/item') as $listElement){
          $attributes = $listElement->attributes();
          $listItem = new \Jazzee\Entity\ElementListItem();
          $listItem->setValue((string)$listElement);
          $listItem->setWeight((string)$attributes['weight']);
          
          if((string)$attributes['active']) $listItem->activate(); else $listItem->deactivate();
          $element->addItem($listItem);
          $this->_em->persist($listItem);
        }
        $this->_em->persist($element);
      }
      
      foreach($xml->xpath('variables/variable') as $element){
        $var = $page->setVar($element['name'], (string)$element);
        $this->_em->persist($var);
      }
      foreach($xml->xpath('children/page') as $element){
        $childPage = $this->addPageFromXml($element);
        $page->addChild($childPage);
      }
    }
    return $page;
  }
  
  
}