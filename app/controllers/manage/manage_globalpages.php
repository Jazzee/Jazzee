<?php
/**
 * Manage Global Pages
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
class ManageGlobalpagesController extends ManageController implements PagesInterface {
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
    $this->addScript('foundation/scripts/form.js');
    $this->addScript('common/scripts/classes/Status.class.js');
    $this->addScript('common/scripts/classes/AuthenticationTimeout.class.js');
    
    $this->addScript('common/scripts/page_types/ApplyPage.class.js');
    $types = Doctrine::getTable('PageType')->findAll(Doctrine::HYDRATE_ARRAY);
    foreach($types as $type){
      $this->addScript("common/scripts/page_types/{$type['class']}.class.js");
    }
    $this->addScript('common/scripts/element_types/ApplyElement.class.js');
    $this->addScript('common/scripts/element_types/ListElement.class.js');
    $this->addScript('common/scripts/element_types/FileInputElement.class.js');
    $types = Doctrine::getTable('ElementType')->findAll(Doctrine::HYDRATE_ARRAY);
    foreach($types as $type){
      $this->addScript("common/scripts/element_types/{$type['class']}.class.js");
    }
    $this->addScript('common/scripts/classes/PageStore.class.js');
    $this->addScript('common/scripts/controllers/manage_globalpages.controller.js');
   
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
    foreach(Doctrine::getTable('Page')->findByIsGlobal(true) AS $page){
      $pages[] = $this->pageArray($page);
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
    $arr['className'] = $page->PageType->class;
    $arr['elements'] = array();
    foreach($page->Elements as $element){
      $e = $element->toArray();
      $e['className'] = $element->ElementType->class;
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
   * Save data from editing a page
   * @param integer $pageId
   */
  public function actionSavePage($pageId){
    $work = new UnitOfWork();
    $data = json_decode($this->post['data']);
    switch($data->status){
      case 'delete':
        if($page = Doctrine::getTable('Page')->findOneByIdAndIsGlobal($pageId,true)){
          if(Doctrine::getTable('ApplicationPage')->findByPageId($page->id)->count()){
            $this->setLayoutVar('status', 'error');
            $this->messages->write('error',"{$page->title} could not be deleted becuase it is part of at least on application");
          } else {
            $page->delete();
            return true;
          }
        }
      break;
      case 'new':
        $page = new Page;
        $page->isGlobal = true;
        $pageType = Doctrine::getTable('PageType')->findOneByClass($data->className);
        $page->pageType = $pageType->id;
        //let the class make modifications if it needs to 
        //no idea why this has to be done in two steps, but it was failing without the interim $className variable
        $className = $page->PageType->class;
        $className::setupNewPage($page);
      default:
        if(!isset($page)) $page = Doctrine::getTable('Page')->findOneByIdAndIsGlobal($pageId,true);
        $page->title = $data->title;
        $page->min = $data->min;
        $page->max = $data->max;
        $page->optional = $data->optional;
        $page->instructions = $data->instructions;
        $page->leadingText = $data->leadingText;
        $page->trailingText = $data->trailingText;
        foreach($data->variables as $v){
          $page->setVar($v->name, $v->value);
        }
        $this->updatePageElements($page, $data->elements, $work);
        foreach($data->children as $child){
          switch($child->status){
            case 'delete':
              $work->registerModelForDelete($page->getChildById($child->pageId));
            break;
            case 'new':
              $childPage = $page->Children->get(null);
              $childPage->isGlobal = false;
              $pageType = Doctrine::getTable('PageType')->findOneByClass($child->className);
              $childPage->pageType = $pageType->id;
            case 'save':
              if(!isset($childPage)) $childPage = $page->getChildById($child->pageId);
              $childPage->title = $child->title;
              $childPage->min = $child->min;
              $childPage->max = $child->max;
              $childPage->optional = $child->optional;
              $childPage->instructions = $child->instructions;
              $childPage->leadingText = $child->leadingText;
              $childPage->trailingText = $child->trailingText;
              foreach($child->variables as $v){
                $childPage->setVar($v->name, $v->value);
              }
              $this->updatePageElements($childPage, $child->elements, $work);
            break;
          }
        }
    } //end switch action
    $work->registerModelForCreateOrUpdate($page);
    $work->commitAll();
  }
  
  /**
   * Update all of the elements on a page with an array of elements passed in
   * @param Element $element
   * @param array $arr array of elements
   * @param UnitOfOwrk $work
   */
  protected function updatePageElements(Page $page, $arr, UnitOfWork $work){
    foreach($arr as $e){
      switch($e->status){
        case 'delete':
          $work->registerModelForDelete($page->getElementByID($e->id));
          break;
        case 'new':
            $element = $page->Elements->get(null);
            $elementType = Doctrine::getTable('ElementType')->findOneByClass($e->className);
            $element->elementType = $elementType->id;
        default:
          if(!isset($element)) $element = $page->getElementByID($e->id);
          $element->title = $e->title;
          $element->format = $e->format;
          $element->instructions = $e->instructions;
          $element->defaultValue = $e->defaultValue;
          $element->required = $e->required;
          $element->min = $e->min;
          $element->max = $e->max;
          foreach($e->list as $i){
            if(!$item = $element->getItemById($i->id)){
              $item = $element->ListItems->get(null);
            }
            $item->value = $i->value;
            $item->active = $i->active;
          }
      }
      unset($element);
    }
  }
  
  /**
   * Preview a page
   */
  public function actionPreviewPage(){
    $data = json_decode($this->post['data']);
    $applicationPage = new ApplicationPage();
    $applicationPage->id = uniqid();
    $this->genericPage($applicationPage->Page, $data);
    $class = new $applicationPage->Page->PageType->class($applicationPage);
    $this->layout = 'blank';
    $this->setVar('page', $class);
  }
  
  /**
   * Create a generic page to use in a preview
   * @param Page $page
   * @param Object $data
   */
  protected function genericPage(Page $page,$data){
    $page->id = uniqid();
    $page->isGlobal = false;
    $pageType = Doctrine::getTable('PageType')->findOneByClass($data->className);
    $page->pageType = $pageType->id;
    $className = $page->PageType->class;
    $className::setupNewPage($page);
    //give any created elements a temporary id so they will display in the form
    foreach($page->Elements as $element){
      $element->id = uniqid();
    }
    $page->title = $data->title;
    $page->min = $data->min;
    $page->max = $data->max;
    $page->optional = $data->optional;
    $page->showAnswerStatus = $data->showAnswerStatus;
    $page->instructions = $data->instructions;
    $page->leadingText = $data->leadingText;
    $page->trailingText = $data->trailingText;
    foreach($data->variables as $v){
      $page->setVar($v->name, $v->value);
    }
    foreach($data->elements as $obj){
      $element = $page->Elements->get(null);
      $this->genericElement($element, $obj);
    }
    foreach($data->children as $obj){
      $childPage = $page->Children->get(null);
      $this->genericPage($childPage, $obj);
    }
  }
  
  /**
   * Crate a generic element to use in previewing a page
   * @param Element $element that we are workign with
   * @param Object $data
   */
  protected function genericElement(Element $element, $data){
    $element->id = uniqid();
    $elementType = Doctrine::getTable('ElementType')->findOneByClass($data->className);
    $element->elementType = $elementType->id;
    $element->title = $data->title;
    $element->format = $data->format;
    $element->instructions = $data->instructions;
    $element->defaultValue = $data->defaultValue;
    $element->required = $data->required;
    $element->min = $data->min;
    $element->max = $data->max;
    foreach($data->list as $i){
      $item = $element->ListItems->get(null);
      $item->id = uniqid();
      $item->value = $i->value;
      $item->active = $i->active;
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
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Manage Global Pages';
    $auth->addAction('index', new ActionAuth('View the Page'));
    $auth->addAction('listPages', new ActionAuth('List Global Pages'));
    $auth->addAction('listPageTypes', new ActionAuth('List the Page Types'));
    $auth->addAction('savePage', new ActionAuth('Edit a Page'));
    $auth->addAction('previewPage', new ActionAuth('Preview a Page'));
    $auth->addAction('listElementTypes', new ActionAuth('List the Element Types'));
    return $auth;
  }
}