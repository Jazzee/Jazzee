<?php
/**
 * Setup the pages
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
class SetupPagesController extends SetupController implements PagesInterface {
  /**
   * Set the default layout to json
   * @var string
   */
  protected $layout = 'json';
  
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
    $this->addScript('common/scripts/status.js');
    $this->addScript('common/scripts/pages/Page.js');
    $this->addScript('common/scripts/pages/Element.js');
    $this->addScript('common/scripts/pages/PageStore.js');
    $this->addScript('common/scripts/pages.js');
   
    $this->addCss('common/styles/pages.css');
    
  }
  
  /**
   * Javascript does the display work unless there is no application
   */
  public function actionIndex(){
    $this->layout = 'wide';
  }
  
  /**
   * List the application Pages
   */
  public function actionListPages(){
    $pages = array();
    foreach($this->application->Pages AS $page){
      $arr = $page->toArray(false);
      $arr['type'] = $page->Page->PageType->class;
      $arr['elements'] = array();
      foreach($page->Page->Elements as $element){
        $e = $element->toArray();
        $e['type'] = $element->ElementType->class;
        $e['list'] = array();
        foreach($element->ListItems as $item){
          $e['list'][] = array(
            'id' => $item->id,
            'value' => $item->value
          );
        }
        $arr['elements'][] = $e;
      }
      $pages[] = $arr;
    }
    $this->setVar('result', $pages);
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * List the available page types
   */
  public function actionListPageTypes(){
    $pageTypes = Doctrine::getTable('PageType')->findAll(Doctrine::HYDRATE_ARRAY);
    foreach($pageTypes as $type){
      $pages[$type['id']] = $type['name'];
    }
    asort($pages);
    $arr = array();
    foreach($pages as $id => $name){
      $arr[] = array(
        'id' => $id,
        'name' => $name
      );
    }
    $this->setVar('result', $arr);
    $this->loadView($this->controllerName . '/result');
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
      $lastPage = end($this->application->Pages->toArray());
      $weight = $lastPage['weight'] + 1;
      $applicationPage = new ApplicationPage;
      $applicationPage->weight = $weight;
      $page = new Page;
      $page->title = "New {$pageType->name} Page";
      $page->optional = false;
      $page->globalPage = false;
      $page->pageType = $pageType->id;
      $applicationPage['Page'] = $page;
      $this->application['Pages'][] = $applicationPage;
      $this->messages->write('success', "Changes Saved");
      $this->application->save();
    }
  }
  
  /**
   * Delete the page
   * @param integer $pageID
   */
  public function actionDeletePage($pageID){
    if(!$page = $this->application->getPageByID($pageID)){
      $this->messages->write('error', "Invalid page: {$pageID}");
    } else {
      $page->Page->delete();
      $this->messages->write('success', "Changes Saved");
      $page->delete();
    }
  }
  
  /**
   * Save data from editing a page
   * @param integer $pageID
   */
  public function actionSavePage($pageID){
    $data = $this->post['data'];
    if(!$page = $this->application->getPageByID($pageID)){
      $this->messages->write('error', "Invalid page: {$pageID}");
    } else {
      $data = replaceNullString($data);
      $page->Page->title = $data['title'];
      $page->Page->min = $data['min'];
      $page->Page->max = $data['max'];
      $page->Page->optional = (bool)$data['optional'];
      $page->Page->instructions = $data['instructions'];
      $page->Page->leadingText = $data['leadingText'];
      $page->Page->trailingText = $data['trailingText'];
      $page->weight = $data['weight'];
      $page->save();
      $elementsByID = array();
      foreach($data['elements'] as $arr){
        if(
          !$element = Doctrine::getTable('Element')->find($arr['id']) or
          (
            $element->Page->ApplicationPage->Application->id != $this->application->id and
            $element->Page->RecommendationPage->ApplicationPage->Application->id != $this->application->id)  
          ){
          $this->messages->write('error', "Invalid Element");
        } else {
          $arr = replaceNullString($arr);
          $element->title = $arr['title'];
          $element->format = $arr['format'];
          $element->instructions = $arr['instructions'];
          $element->defaultValue = $arr['defaultValue'];
          $element->required = (bool)$arr['required'];
          $element->min = $arr['min'];
          $element->max = $arr['max'];
          $items = array();
          foreach($arr['list'] as $item){
            $items[$item['id']] = $item['value'];
          }
          foreach($element->ListItems as $item){
            if(array_key_exists($item->id, $items)){
              $item->value = $items[$item->id];
            }
          }
          $element->save();
        }
      }
      $this->messages->write('success', "Changes Saved");
    }
  }
  
  /**
   * Preview a page
   * @param integer $pageID
   */
  public function actionPreviewPage($pageID){
    if($page = $this->application->getPageByID($pageID)){
      $class = new $page->Page->PageType->class($page);
      $this->layout = 'blank';
      $this->setVar('page', $class);
    }
  }
  
  /**
   * List the available element types
   */
  public function actionListElementTypes(){
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
    $this->setVar('result', $arr);
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Add an element to the page
   * @param integer $pageID
   */  
  public function actionAddElement($pageID){
    $elementType = Doctrine::getTable('ElementType')->find($this->post['type']);
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
        $this->messages->write('error', "Invalid page");
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
   * Delete the page
   * @param integer $pageID
   */
  public function actionDeleteElement($elementID){
    if(
      !$element = Doctrine::getTable('Element')->find($elementID) or
      (
        $element->Page->ApplicationPage->Application->id != $this->application->id and
        $element->Page->RecommendationPage->ApplicationPage->Application->id != $this->application->id)  
      ){
      $this->messages->write('error', "Invalid Element");
    } else {
      $this->messages->write('success', "Element Deleted");
      $element->delete();
    }
  }
  
  /**
   * Add a list item
   * @param $pageID
   * @param $elementID
   */
  public function actionAddListItem($pageID, $elementID){
    if(
      !$element = Doctrine::getTable('Element')->find($elementID) or
      (
        $element->Page->ApplicationPage->Application->id != $this->application->id and
        $element->Page->RecommendationPage->ApplicationPage->Application->id != $this->application->id)  
      ){
      $this->messages->write('error', "Invalid Element");
    } else {
      $this->messages->write('success', "List Item Added");
      $item = $element->ListItems->get(null);
      $item->value = $this->post['value'];
      $item->save();
    }
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