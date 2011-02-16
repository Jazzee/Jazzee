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
    $this->addScript('common/scripts/authenticationTimeout.js');
    
    $this->addScript('common/scripts/page_types/ApplyPage.class.js');
    $types = Doctrine::getTable('PageType')->findAll(Doctrine::HYDRATE_ARRAY);
    foreach($types as $type){
      $this->addScript("common/scripts/page_types/{$type['class']}.class.js");
    }
    $this->addScript('common/scripts/element_types/ApplyElement.class.js');
    $this->addScript('common/scripts/element_types/ListElement.class.js');
    $types = Doctrine::getTable('ElementType')->findAll(Doctrine::HYDRATE_ARRAY);
    foreach($types as $type){
      $this->addScript("common/scripts/element_types/{$type['class']}.class.js");
    }
    $this->addScript('common/scripts/classes/PageStore.class.js');
    $this->addScript('common/scripts/controllers/setup_pages.controller.js');
   
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
   * List the global Pages
   */
  public function actionListGlobalPages(){
    $pages = array();
    foreach(Doctrine::getTable('Page')->findByIsGlobal(true) AS $page){
      $pages[] = $this->pageArray($page);
    }
    $this->setVar('result', $pages);
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Save data from editing a page
   * @param integer $applicationPageId
   */
  public function actionSavePage($applicationPageId){
    $work = new UnitOfWork();
    $data = json_decode($this->post['data']);
    switch($data->status){
      case 'delete':
        if($page = $this->application->getPageByID($applicationPageId)){
          if($page->Page->isGlobal){
            $work->registerModelForDelete($page);
          } else {
            $work->registerModelForDelete($page->Page);
          }
        }
      break;
      case 'new-global':
        $applicationPage = $this->application->Pages->get(null);
        $applicationPage->pageID = $data->pageId;
        $applicationPage->title = $data->title;
        $applicationPage->min = $data->min;
        $applicationPage->max = $data->max;
        $applicationPage->optional = $data->optional;
        $applicationPage->Page->showAnswerStatus = $data->showAnswerStatus;
        $applicationPage->instructions = $data->instructions;
        $applicationPage->leadingText = $data->leadingText;
        $applicationPage->trailingText = $data->trailingText;
        break;
      case 'new':
        $applicationPage = $this->application->Pages->get(null);
        $applicationPage->Page->isGlobal = false;
        $pageType = Doctrine::getTable('PageType')->findOneByClass($data->className);
        $applicationPage->Page->pageType = $pageType->id;
        //let the class make modifications if it needs to 
        //no idea why this has to be done in two steps, but it was failing without the interim $className variable
        $className = $applicationPage->Page->PageType->class;
        $className::setupNewPage($applicationPage->Page);
      default:
        if(!isset($applicationPage)) $applicationPage = $this->application->getPageByID($applicationPageId);
        $applicationPage->title = $data->title;
        $applicationPage->min = $data->min;
        $applicationPage->max = $data->max;
        $applicationPage->optional = $data->optional;
        $applicationPage->Page->showAnswerStatus = $data->showAnswerStatus;
        $applicationPage->instructions = $data->instructions;
        $applicationPage->leadingText = $data->leadingText;
        $applicationPage->trailingText = $data->trailingText;
        $applicationPage->weight = $data->weight;
        if(!$applicationPage->Page->isGlobal){
          foreach($data->variables as $v){
            $applicationPage->Page->setVar($v->name, $v->value);
          }
          $this->updatePageElements($applicationPage->Page, $data->elements, $work);
          foreach($data->children as $child){
            switch($child->status){
              case 'delete':
                $work->registerModelForDelete($applicationPage->Page->getChildById($child->pageId));
              break;
              case 'new':
                $childPage = $applicationPage->Page->Children->get(null);
                $childPage->isGlobal = false;
                $pageType = Doctrine::getTable('PageType')->findOneByClass($child->className);
                $childPage->pageType = $pageType->id;
              case 'save':
                if(!isset($childPage)) $childPage = $applicationPage->Page->getChildById($child->pageId);
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
        }
    } //end switch action
    $work->registerModelForCreateOrUpdate($this->application);
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