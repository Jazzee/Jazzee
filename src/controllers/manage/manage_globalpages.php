<?php
/**
 * Manage Global Pages
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
class ManageGlobalpagesController extends \Jazzee\AdminController {
  const MENU = 'Manage';
  const TITLE = 'Global Pages';
  const PATH = 'manage/globalpages';
  
  const ACTION_INDEX = 'Edit Global Pages';
  
  /**
   * Add the required JS
   */
  public function setUp(){
    $this->layout = 'json';
    $this->addScript($this->path('resource/foundation/scripts/form.js'));
    $this->addScript($this->path('resource/scripts/classes/Status.class.js'));
    $this->addScript($this->path('resource/scripts/classes/AuthenticationTimeout.class.js'));
    $this->addScript($this->path('resource/scripts/page_types/ApplyPage.class.js'));
    
    $types = $this->_em->getRepository('\Jazzee\Entity\PageType')->findAll();
    foreach($types as $type){
      $class = \explode('\\', $type->getClass());
      $class = $class[count($class) - 1];
      $this->addScript($this->path('resource/scripts/page_types/' . $class . 'Page.class.js'));
    }
    $this->addScript($this->path('resource/scripts/element_types/ApplyElement.class.js'));
    $this->addScript($this->path('resource/scripts/element_types/ListElement.class.js'));
    $this->addScript($this->path('resource/scripts/element_types/FileInputElement.class.js'));
    
    $types = $this->_em->getRepository('\Jazzee\Entity\ElementType')->findAll();
    foreach($types as $type){
      $class = \explode('\\', $type->getClass());
      $class = $class[count($class) - 1];
      $this->addScript($this->path('resource/scripts/element_types/' . $class . 'Element.class.js'));
    }
    $this->addScript($this->path('resource/scripts/classes/PageStore.class.js'));
    $this->addScript($this->path('resource/scripts/controllers/manage_globalpages.controller.js'));
    
    
    $this->addCss($this->path('resource/styles/pages.css'));
    
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
    foreach($this->_em->getRepository('\Jazzee\Entity\Page')->findByIsGlobal(true) AS $page){
      $pages[] = $this->pageArray($page);
    }
    $this->setVar('result', $pages);
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Create an array from a page suitable for json_encoding
   * @param \Jazzee\Entity\Page $page
   * @return array
   */
  protected function pageArray(\Jazzee\Entity\Page $page){
    $arr = array(
      'pageId' => $page->getId(),
      'title' => $page->getTitle(),
      'min' => $page->getMin(),
      'max' => $page->getMax(),
      'isRequired' => $page->isRequired(),
      'answerStatusDisplay' => $page->answerStatusDisplay(),
      'instructions' => $page->getInstructions(),
      'leadingText' => $page->getLeadingText(),
      'trailingText' => $page->getTrailingText(),
    );
    $class = \explode('\\', $page->getType()->getClass());
    $class = $class[count($class) - 1];
    $arr['className'] = $class . 'Page';
    $arr['elements'] = array();
    foreach($page->getElements() as $element){
      $e = array(
        'id' => $element->getId(),
        'weight' => $element->getWeight(),
        'title' => $element->getTitle(),
        'format' => $element->getFormat(),
        'min' => $element->getMin(),
        'max' => $element->getMax(),
        'isRequired' => $element->isRequired(),
        'instructions' => $element->getInstructions(),
        'defaultValue' => $element->getDefaultValue()
      );
      
      $class = \explode('\\', $element->getType()->getClass());
      $class = $class[count($class) - 1];
      $e['className'] = $class . 'Element';
      $e['list'] = array();
      foreach($element->getListItems() as $item){
        $e['list'][] = array(
          'id' => $item->getId(),
          'value' => $item->getValue(),
          'active' => (int)$item->isActive()
        );
      }
      $arr['elements'][] = $e;
    }
    $arr['variables'] = array();
    foreach($page->getVariables() as $variable){
      $arr['variables'][] = array(
        'name' => $variable->getName(),
        'value' => $variable->getValue()
      );
    }
    $arr['children'] = array();
    foreach($page->getChildren() as $child){
      $arr['children'][] = $this->pageArray($child);
    }
    return $arr;
  }
  
  /**
   * List the available page types
   */
  public function actionListPageTypes(){
    $pageTypes = $this->_em->getRepository('\Jazzee\Entity\PageType')->findAll();
    $pages = array();
    foreach($pageTypes as $type){
      $pages[$type->getName()] = $type->getId();
      $pageTypes[$type->getId()] = $type;
    }
    //alphabetize the page types
    ksort($pages);
    $arr = array();
    foreach($pages as $id){
      $arr[] = array(
        'id' => $id,
        'name' => $pageTypes[$id]->getName(),
        'class' => $pageTypes[$id]->getClass(),
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
            $this->addMessage('error',"{$page->title} could not be deleted becuase it is part of at least on application");
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
  
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null){
    //all action authorizations are controlled by the index action
    return parent::isAllowed($controller, 'index', $user, $program);
  }
}