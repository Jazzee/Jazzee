<?php
/**
 * Setup the pages
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
class SetupPagesController extends SetupController {
  
  /**
   * Add the required JS
   */
  public function setUp(){
    parent::setUp();
    if($this->application === false){
      $this->messages->write('error', 'Please setup the application before creating pages');
      $this->redirect($this->path("setup/application"));
      $this->afterAction();
      exit();
    }
    $this->addScript('foundation/scripts/form.js');
    $this->addScript('common/scripts/messages.js');
    $this->addScript('common/scripts/pages.js');
    $this->addCss('common/styles/pages.css');
    
  }
  
  /**
   * Javascript does the display work unless there is no application
   */
  public function actionIndex(){
  }
  
  /**
   * List the application Pages
   */
  public function actionPageList(){
    $pages = array();
    foreach($this->application->Pages AS $page){
      $pages[] = array(
        'id' => $page->id,
        'title' => $page->title
      );
    }
    $this->layout = 'json';
    $this->setVar('pages', $pages);
  }
  
  /**
   * List the available page types
   */
  public function actionNewPageList(){
    $pageTypes = Doctrine::getTable('PageType')->findAll(Doctrine::HYDRATE_ARRAY);
    foreach($pageTypes as $page){
      $pages[$page['id']] = $page['name'];
    }
    asort($pages);
    $arr = array();
    foreach($pages as $id => $name){
      $arr[] = array(
        'id' => $id,
        'name' => $name
      );
    }
    $this->layout = 'json';
    $this->setVar('pages', $arr);
  }
  
  /**
   * Add a new page
   */
  public function actionAddPage(){
    $pageType = Doctrine::getTable('PageType')->find($this->post['pageType']);
    if(!$pageType or !class_exists($pageType->class)){
      $this->setLayoutVar('status', 'error');
      $this->messages->write('error', "Invalid page type");
    } else {
      $applicationPage = new ApplicationPage;
      $page = new Page;
      $page->title = "New {$pageType->name} Page";
      $page->min = 1;
      $page->max = 1;
      $page->optional = false;
      $page->pageType = $pageType->id;
      $applicationPage['Page'] = $page;
      $this->application['Pages'][] = $applicationPage;
      $this->application->save();
    }
    $this->layout = 'json';
  }
  
  /**
   * Get all of the tabs for a page
   * @param integer $pageID
   */
  public function actionGetTabs($pageID){
    if(!$page = $this->application->getPageByID($pageID)){
      $this->messages->write('error', "Invalid page: {$pageID}");
    } else {
      $this->setVar('page', $page);
    }
    $this->layout = 'json';
  }
  
  /**
   * Post the input from a page cretor form
   * @param string $name the name of the form
   * @param integer $pageID
   */
  public function actionPostForm($name, $pageID){
    if(!$page = $this->application->getPageByID($pageID)){
      $this->messages->write('error', "Invalid page: {$pageID}");
    } else {
      $class = new $page->Page->PageType->class($page);
      $tabs = $class->getTabs();
      $form = $tabs[$name]->getForm();
      if($input = $form->processInput($this->post)){
        $class->{$tabs[$name]->method}($input);
        $tabs = $class->getTabs();
        $form = $tabs[$name]->getForm();
      }
      $form->action = $this->path("setup/pages/postForm/{$name}/{$pageID}");
      $this->setVar('form', $form);
    }
    $this->layout = 'json';
  }
  
  /**
   * Delete the page
   * @param integer $pageID
   */
  public function actionDelete($pageID){
    if(!$page = $this->application->getPageByID($pageID)){
      $this->messages->write('error', "Invalid page: {$pageID}");
      $this->layout = 'json';
    } else {
      if(!$page->Page->GlobalPage->count()){
        $page->Page->delete();
      }
      $page->delete();
    }
    $this->layout = 'json';
  }
  
  /**
   * List the available element types
   */
  public function actionNewElementsList(){
    $elements = array();
    $elementTypes = Doctrine::getTable('ElementType')->findAll(Doctrine::HYDRATE_ARRAY);
    foreach($elementTypes as $element){
      $elements[$element['id']] = $element['name'];
    }
    asort($elements);
    $arr = array();
    foreach($elements as $id => $name){
      $arr[] = array(
        'id' => $id,
        'name' => $name
      );
    }
    $this->layout = 'json';
    $this->setVar('elements', $arr);
  }
  
  /**
   * Add an element to the page
   * @param integer $pageID
   */  
  public function actionAddElement($pageID){
    $this->layout = 'json';
    $elementType = Doctrine::getTable('ElementType')->find($this->post['elementType']);
    if(!$elementType or !class_exists($elementType->class)){
      $this->setLayoutVar('status', 'error');
      $this->messages->write('error', "Invalid element type");
    } else {
      if(
        !$page = Doctrine::getTable('Page')->find($pageID) or
        ($page->ApplicationPage->Application->id != $this->application->id and
        $page->RecommendationPage->ApplicationPage->Application->id != $this->application->id)  
      ){
        $this->setLayoutVar('status', 'error');
        $this->messages->write('error', "Invalid page: {$pageID}");
      } else {
        $this->messages->write('success', "Element Added Successfully");
        $element = new Element;
        $element->elementType = $elementType->id;
        $element->title = "New {$elementType->name} Element";
        $page->Elements[] = $element;
        $page->save();
      }
    }
  }

  /**
   * Edit an element
   * @param integer $elementID
   */
  public function actionEditElement($elementID){
    $element = Doctrine::getTable('Element')->find($elementID);
    if(
      !$element or
      (
        !$page = Doctrine::getTable('Page')->find($element->Page->id) or
        ($page->ApplicationPage->Application->id != $this->application->id and
        $page->RecommendationPage->ApplicationPage->Application->id != $this->application->id)  
      )){
      $this->messages->write('error', "Invalid Element: {$elementID}");
    } else {
      $e = new $element->ElementType->class($element);
      $form = $e->getPropertiesForm();
      if($input = $form->processInput($this->post)){
        $e->setProperties($input);
        $this->messages->write('success', "Changes Saved Successfully");
        $form = $e->getPropertiesForm();
      }
      $form->action = $this->path("setup/pages/editElement/{$elementID}");
      $this->setVar('form', $form);
    }
    $this->layout = 'json';
  }
  
  /**
   * Deactivate List Item
   * @param integer $listItemID
   */  
  public function actionDeactivateListItem($listItemID){
    $listItem = Doctrine::getTable('ElementListItem')->find($listItemID);
    if(!$listItem or
      (
        !$page = Doctrine::getTable('Page')->find($listItem->Element->Page->id) or
        ($page->ApplicationPage->Application->id != $this->application->id and
        $page->RecommendationPage->ApplicationPage->Application->id != $this->application->id)  
      )){
      $this->messages->write('error', "Invalid List Item: {$listItemID}");
    } else {
      $listItem->active = false;
      $listItem->save();
    }
    $this->layout = 'json';
  }
  
  /**
   * Activate List Item
   * @param integer $listItemID
   */  
  public function actionActivateListItem($listItemID){
    $listItem = Doctrine::getTable('ElementListItem')->find($listItemID);
    if(!$listItem or
      (
        !$page = Doctrine::getTable('Page')->find($listItem->Element->Page->id) or
        ($page->ApplicationPage->Application->id != $this->application->id and
        $page->RecommendationPage->ApplicationPage->Application->id != $this->application->id)  
      )){
      $this->messages->write('error', "Invalid List Item: {$listItemID}");
    } else {
      $listItem->active = true;
      $listItem->save();
    }
    $this->layout = 'json';
  }
  
  /**
   * Add a list item to an element
   * @param integer $elementID
   */  
  public function actionAddListItem($elementID){
    $element = Doctrine::getTable('Element')->find($elementID);
    if(
      !$element or
      (
        !$page = Doctrine::getTable('Page')->find($element->Page->id) or
        ($page->ApplicationPage->Application->id != $this->application->id and
        $page->RecommendationPage->ApplicationPage->Application->id != $this->application->id)  
      )){
      $this->messages->write('error', "Invalid Element: {$elementID}");
    } else {
      $item = $element->ListItems->get(null);
      $item->value = $this->post['value'];
      $element->save();
    }
    $this->layout = 'json';
  }
  
  public static function isAllowed($controller, $action, $user, $programID, $cycleID, $actionParams){
    $action = 'index'; //all action authorizations are controlled by the index action
    if($programID AND $cycleID AND $user)  return $user->isAllowed($controller, $action, $programID);
    return false;
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Setup Pages';
    $auth->addAction('index', new ActionAuth('Make Changes'));
    return $auth;
  }
}