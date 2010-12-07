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
    foreach($this->application->Pages AS $applicationPage){
      $arr = $this->pageArray($applicationPage->Page);
      $arr['weight'] = $applicationPage->weight;
      $arr['applicationPageId'] = $applicationPage->id;
      if($applicationPage->Page->isGlobal){
        //use the values in AplicationPage instead of Page
        $arr['title'] = $applicationPage->title;
        $arr['min'] = $applicationPage->min;
        $arr['max'] = $applicationPage->max;
        $arr['optional'] = $applicationPage->optional;
        $arr['instructions'] = $applicationPage->instructions;
        $arr['leadingText'] = $applicationPage->leadingText;
        $arr['trailingText'] = $applicationPage->trailingText;
      }
      $pages[] = $arr;
    }
    $this->setVar('result', $pages);
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Create an array from a page suitable for json_encoding
   * @param Page $page
   * @return array
   */
  protected function pageArray(Page $page){
    $arr = $page->toArray(false);
    $arr['pageId'] = $arr['id'];
    $arr['type'] = $page->PageType->class;
    $arr['elements'] = array();
    foreach($page->Elements as $element){
      $e = $element->toArray();
      $e['type'] = $element->ElementType->class;
      $e['list'] = array();
      foreach($element->ListItems as $item){
        $e['list'][] = array(
          'id' => $item->id,
          'value' => $item->value,
          'active' => (int)$item->active
        );
      }
      $arr['elements'][] = $e;
    }
    $arr['variables'] = array();
    foreach($page->Variables as $variable){
      $arr['variables'][] = $variable->toArray();
    }
    
    $arr['children'] = array();
    foreach($page->Children as $child){
      $arr['children'][] = $this->pageArray($child);
    }
    return $arr;
  }
  
  /**
   * List the available page types
   */
  public function actionListPageTypes(){
    $pageTypes = Doctrine::getTable('PageType')->findAll(Doctrine::HYDRATE_ARRAY);
    $pages = array();
    foreach($pageTypes as $type){
      $pages[$type['name']] = $type['id'];
      $pageTypes[$type['id']] = $type;
    }
    //alphabetize the page types
    ksort($pages);
    $arr = array();
    foreach($pages as $id){
      $arr[] = array(
        'id' => $id,
        'name' => $pageTypes[$id]['name'],
        'class' => $pageTypes[$id]['class'],
      );
    }
    $this->setVar('result', $arr);
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Delete the page
   * @param integer $applicationPageId
   */
  public function actionDeletePage($applicationPageId){
    if(!$page = $this->application->getPageByID($applicationPageId)){
      $this->messages->write('error', "Invalid page");
    } else {
      $title = $page->title;
      if($page->Page->isGlobal){
        $page->delete();
      } else {
        $page->Page->delete();
      }
      $this->messages->write('success', "{$title} Deleted");
    }
  }
  
  /**
   * Save data from editing a page
   * @param integer $applicationPageId
   */
  public function actionSavePage($applicationPageId){
    $data = $this->post['data'];
    if(!$applicationPage = $this->application->getPageByID($applicationPageId)){
      $pageType = Doctrine::getTable('PageType')->find($data['pageType']);
      if(!$pageType or !class_exists($pageType->class)){
        $this->messages->write('error', "Invalid Page Type");
        return false;
      }
      $applicationPage = $this->application->Pages->get(null);
      $page = $applicationPage->Page;
      $page->isGlobal = false;
      $page->pageType = $pageType->id;
      //let the class make modifications if it needs to 
      //no idea why this has to be done in two steps, but it was failing without the interim $className variable
      $className = $page->PageType->class;
      $className::setupNewPage($page);
    }
    $data = replaceNullString($data);
    $applicationPage->title = $data['title'];
    $applicationPage->min = $data['min'];
    $applicationPage->max = $data['max'];
    $applicationPage->optional = (bool)$data['optional'];
    $applicationPage->instructions = $data['instructions'];
    $applicationPage->leadingText = $data['leadingText'];
    $applicationPage->trailingText = $data['trailingText'];
    $applicationPage->save();
    $elementIds = array();
    foreach($data['elements'] as $arr){
      $elementIds[] = $arr['id'];
    }
    //get rid of any elements that are no longer present
    foreach($applicationPage->Page->Elements as $element){
      if(!in_array($element->id, $elementIds)) $element->delete();
    }
    foreach($data['elements'] as $arr){
      if(!$element = $applicationPage->Page->getElementByID($arr['id'])){
        $elementType = Doctrine::getTable('ElementType')->find($arr['elementType']);
        if(!$elementType or !class_exists($elementType->class)){
          $this->messages->write('error', "Invalid Element Type");
          return false;
        }
        $element = $applicationPage->Page->Elements->get(null);
        $element->elementType = $elementType->id;
      }
      $arr = replaceNullString($arr);
      $element->title = $arr['title'];
      $element->format = $arr['format'];
      $element->instructions = $arr['instructions'];
      $element->defaultValue = $arr['defaultValue'];
      $element->required = (bool)$arr['required'];
      $element->min = $arr['min'];
      $element->max = $arr['max'];
      $element->save();
      foreach($arr['list'] as $itemArr){
        if(!$item = $element->getItemById($itemArr['id'])){
          $item = $element->ListItems->get(null);
        }
        $item->value = $itemArr['value'];
        $item->active = $itemArr['active'];
        $item->save();
      }
    }
    
    //for children pages
    foreach($data['children'] as $childArr){
      if(!$page = $applicationPage->Page->getChildById($childArr['pageId'])){
        $pageType = Doctrine::getTable('PageType')->find($childArr['pageType']);
        if(!$pageType or !class_exists($pageType->class)){
          $this->messages->write('error', "Invalid Page Type for Child page");
          return false;
        }
        $page = $applicationPage->Page->Children->get(null);
        $page->isGlobal = false;
        $page->pageType = $pageType->id;
        //let the class make modifications if it needs to 
        //no idea why this has to be done in two steps, but it was failing without the interim $className variable
        $className = $page->PageType->class;
        $className::setupNewPage($page);
      }
      $childArr = replaceNullString($childArr);
      $page->title = $childArr['title'];
      $page->min = $childArr['min'];
      $page->max = $childArr['max'];
      $page->optional = (bool)$childArr['optional'];
      $page->instructions = $childArr['instructions'];
      $page->leadingText = $childArr['leadingText'];
      $page->trailingText = $childArr['trailingText'];
      $page->save();
      $elementIds = array();
      foreach($childArr['elements'] as $arr){
        $elementIds[] = $arr['id'];
      }
      //get rid of any elements that are no longer present
      foreach($page->Elements as $element){
        if(!in_array($element->id, $elementIds)) $element->delete();
      }
      foreach($childArr['elements'] as $arr){
        if(!$element = $page->getElementByID($arr['id'])){
          $elementType = Doctrine::getTable('ElementType')->find($arr['elementType']);
          if(!$elementType or !class_exists($elementType->class)){
            $this->messages->write('error', "Invalid Element Type in Child Page");
            return false;
          }
          $element = $page->Elements->get(null);
          $element->elementType = $elementType->id;
        }
        $arr = replaceNullString($arr);
        $element->title = $arr['title'];
        $element->format = $arr['format'];
        $element->instructions = $arr['instructions'];
        $element->defaultValue = $arr['defaultValue'];
        $element->required = (bool)$arr['required'];
        $element->min = $arr['min'];
        $element->max = $arr['max'];
        $element->save();
        foreach($arr['list'] as $itemArr){
          if(!$item = $element->getItemById($itemArr['id'])){
            $item = $element->ListItems->get(null);
          }
          $item->value = $itemArr['value'];
          $item->active = $itemArr['active'];
          $item->save();
        }
      }
    }
    $this->application->save();
    $this->messages->write('success', "{$applicationPage->title} Saved");
  }
  
  /**
   * Preview a page
   * @param integer $applicationPageId
   */
  public function actionPreviewPage($applicationPageId){
    if($page = $this->application->getPageByID($applicationPageId)){
      $class = new $page->Page->PageType->class($page);
      $this->layout = 'blank';
      $this->setVar('page', $class);
    }
  }

  /**
   * List the available element types
   */
  public function actionListElementTypes(){
    $elementTypes = Doctrine::getTable('ElementType')->findAll(Doctrine::HYDRATE_ARRAY);
    $elements = array();
    foreach($elementTypes as $type){
      $elements[$type['name']] = $type['id'];
      $elementTypes[$type['id']] = $type;
    }
    //alphabetize the page types
    ksort($elements);
    $arr = array();
    foreach($elements as $id){
      $arr[] = array(
        'id' => $id,
        'name' => $elementTypes[$id]['name'],
        'class' => $elementTypes[$id]['class'],
      );
    }
    $this->setVar('result', $arr);
    $this->loadView($this->controllerName . '/result');
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