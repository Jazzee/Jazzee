<?php
namespace Jazzee;
/**
 * PageBuilder
 * 
 * The page builder abstracts common functionality between application pages and global pages
 */
abstract class PageBuilder extends AdminController{
  
  /**
   * Add the required JS
   */
  public function setUp(){
    //everything is displayed over json
    $this->layout = 'json';
    
    $this->addScript($this->path('resource/foundation/scripts/form.js'));
    $this->addScript($this->path('resource/scripts/classes/Status.class.js'));
    $this->addScript($this->path('resource/scripts/classes/AuthenticationTimeout.class.js'));
    $this->addScript($this->path('resource/scripts/page_types/JazzeePage.js'));
    
    $types = $this->_em->getRepository('\Jazzee\Entity\PageType')->findAll();
    foreach($types as $type){
      $this->addScript($this->path('resource/scripts/page_types/' . $this->getClassName($type->getClass()) . '.js'));
    }
    
    $this->addScript($this->path('resource/scripts/element_types/JazzeeElement.js'));
    $this->addScript($this->path('resource/scripts/element_types/List.js'));
    $this->addScript($this->path('resource/scripts/element_types/FileInput.js'));
    
    $types = $this->_em->getRepository('\Jazzee\Entity\ElementType')->findAll();
    foreach($types as $type){
      $this->addScript($this->path('resource/scripts/element_types/' . $this->getClassName($type->getClass()) . '.js'));
    }
    $this->addScript($this->path('resource/scripts/classes/PageStore.class.js'));
    $this->addCss($this->path('resource/styles/pages.css'));
    
    
    require_once 'HTMLPurifier.includes.php';
    require_once 'HTMLPurifier.autoload.php';
    if(!class_exists('HTMLPurifier')){
      throw new \Foundation\Exception('HTML Purifier is required for building pages and it is not available.');
    }
    
  }
  
  /**
   * Return a list of all the current pages
   */
  abstract public function actionListPages();
  
  /**
   * Take the input from a save page requires
   * 
   * @param integer $pageId
   */
  abstract public function actionSavePage($pageId);
  
  /**
   * Javascript does the display work unless there is no application
   */
  public function actionIndex(){
    $this->layout = 'wide';
  }
  
  /**
   * Create an array from a page suitable for json_encoding
   * @param \Jazzee\Entity\Page of \Jazzee\Entity\ApplicationPage $page
   * @return array
   */
  protected function pageArray($page){
    $arr = array(
      'title' => $page->getTitle(),
      'min' => is_null($page->getMin())?0:$page->getMin(),
      'max' => is_null($page->getMax())?0:$page->getMax(),
      'isRequired' => (int)$page->isRequired(),
      'answerStatusDisplay' => $page->answerStatusDisplay()?1:0,
      'instructions' => $page->getInstructions(),
      'leadingText' => $page->getLeadingText(),
      'trailingText' => $page->getTrailingText(),
    );
    
    //now that we have completed the general setup replace $applicationPage with $page
    if($page instanceof \Jazzee\Entity\ApplicationPage){
      $arr['weight'] = $page->getWeight();
      $page = $page->getPage();
    } 
    $arr['id'] = $page->getId();
    $arr['className'] = $this->getClassName($page->getType()->getClass());
    $arr['classId'] = $page->getType()->getId();
    $arr['elements'] = array();
    foreach($page->getElements() as $element){
      $e = array(
        'id' => $element->getId(),
        'weight' => $element->getWeight(),
        'title' => $element->getTitle(),
        'format' => $element->getFormat(),
        'min' => is_null($element->getMin())?0:$element->getMin(),
        'max' => is_null($element->getMax())?0:$element->getMax(),
        'isRequired' => (int)$element->isRequired(),
        'instructions' => $element->getInstructions(),
        'defaultValue' => $element->getDefaultValue()
      );
      $e['className'] = $this->getClassName($element->getType()->getClass());
      $e['classId'] = $element->getType()->getId();
      $e['list'] = array();
      foreach($element->getListItems() as $item){
        $e['list'][] = array(
          'id' => $item->getId(),
          'value' => $item->getValue(),
          'weight' => $item->getWeight(),
          'isActive' => (int)$item->isActive()
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
        'className' => $this->getClassName($pageTypes[$id]->getClass()),
      );
    }
    $this->setVar('result', $arr);
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * List the available element types
   */
  public function actionListElementTypes(){
    $elementTypes = $this->_em->getRepository('\Jazzee\Entity\ElementType')->findAll();
    $elements = array();
    foreach($elementTypes as $type){
      $elements[$type->getName()] = $type->getId();
      $elementTypes[$type->getId()] = $type;
    }
    //alphabetize the page types
    ksort($elements);
    $arr = array();
    foreach($elements as $id){
      $arr[] = array(
        'id' => $id,
        'name' => $elementTypes[$id]->getName(),
        'className' => $this->getClassName($elementTypes[$id]->getClass()),
      );
    }
    $this->setVar('result', $arr);
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * List the available payment types
   */
  public function actionListPaymentTypes(){
    $paymentTypes = $this->_em->getRepository('\Jazzee\Entity\PaymentType')->findBy(array('isExpired'=>false));
    $types = array();
    foreach($paymentTypes as $type){
      $types[$type->getName()] = $type->getId();
      $paymentTypes[$type->getId()] = $type;
    }
    //alphabetize the page types
    ksort($types);
    $arr = array();
    foreach($types as $id){
      $arr[] = array(
        'id' => $id,
        'name' => $paymentTypes[$id]->getName(),
      );
    }
    $this->setVar('result', $arr);
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Save a page
   * @param \Jazzee\Entity\Page $page
   */
  public function savePage($page, $data){
    $htmlPurifier = new \HTMLPurifier();
    
    $page->setTitle($htmlPurifier->purify($data->title));
    $page->setMin(empty($data->min)?null:$data->min);
    $page->setMax(empty($data->max)?null:$data->max);
    if($data->isRequired) $page->required(); else $page->optional();
    if($data->answerStatusDisplay) $page->showAnswerStatus(); else $page->hideAnswerStatus();
    $page->setInstructions(empty($data->instructions)?null:$htmlPurifier->purify($data->instructions));
    $page->setLeadingText(empty($data->leadingText)?null:$htmlPurifier->purify($data->leadingText));
    $page->setTrailingText(empty($data->trailingText)?null:$htmlPurifier->purify($data->trailingText));
    
    $this->_em->persist($page);
    
    if($page instanceof \Jazzee\Entity\ApplicationPage){
      $page->setWeight($data->weight);
      //if this is a global page then we are done
      //programs can't edit any of the remaining properties on a globa page
      if($page->getPage()->isGlobal()){
        return;
      }
      //otherwise continue making changes by swaping the $page varialbe for the correct \Jazzee\Entity\Page class
      $page = $page->getPage();
    }
    foreach($data->variables as $v){
      $jazzeePage = $page->getApplicationPageJazzeePage();
      $jazzeePage->setController($this);
      $jazzeePage->setVar($v->name, $v->value);
    }
    $this->savePageElements($page, $data->elements);
    foreach($data->children as $child){
      switch($child->status){
        case 'delete':
          $this->_em->delete($page->getChildById($child->id));
          $page->getChildren()->remove($child->id);
        break;
        case 'new':
          $childPage = new \Jazzee\Entity\Page();
          $childPage->setParent($page);
          $childPage->notGlobal();
          $childPage->setType($this->_em->getRepository('\Jazzee\Entity\PageType')->find($child->classId));
        default:
          if(!isset($childPage)) $childPage = $page->getChildById($child->id);
          $this->savePage($childPage, $child);
        break;
      }
      unset($childPage);
    }
  }
  
  /**
   * Update all of the elements on a page with an array of elements passed in
   * @param \Jazzee\Entity\Page $page
   * @param array $elements
   */
  protected function savePageElements(\Jazzee\Entity\Page $page, array $elements){
    $htmlPurifier = new \HTMLPurifier();
    foreach($elements as $e){
      switch($e->status){
        case 'delete':
          //don't try and delete temporary elements
          if($element = $page->getElementByID($e->id)){
            $this->_em->remove($element);
            $page->getElements()->remove($element->getId());
          }
          break;
        case 'new':
            $element = new \Jazzee\Entity\Element();
            $page->addElement($element);
            $element->setType($this->_em->getRepository('\Jazzee\Entity\ElementType')->find($e->classId));
        default:
          if(!isset($element)) $element = $page->getElementByID($e->id);
          $element->setWeight($e->weight);
          $element->setTitle($htmlPurifier->purify($e->title));
          $element->setFormat(empty($e->format)?null:$htmlPurifier->purify($e->format));
          $element->setInstructions(empty($e->instructions)?null:$htmlPurifier->purify($e->instructions));
          $element->setDefaultValue(empty($e->defaultValue)?null:$htmlPurifier->purify($e->defaultValue));
          if($e->isRequired) $element->required(); else $element->optional();
          $element->setMin(empty($e->min)?null:$e->min);
          $element->setMax(empty($e->max)?null:$e->max);
          foreach($e->list as $i){
            if(!$item = $element->getItemById($i->id)){
              $item = new \Jazzee\Entity\ElementListItem();
              $element->addItem($item);
            }
            $item->setValue($htmlPurifier->purify($i->value));
            $item->setWeight($i->weight);
            if($i->isActive) $item->activate(); else $item->deActivate();
            $this->_em->persist($item);
          }
          $this->_em->persist($element);
      }
      unset($element); //this isn't for memory management if it stays set it gets re-used at the begning of the default switch
    }
  }

  /**
   * Preview a page
   * 
   * We use a fake page to construct a preview
   */
  public function actionPreviewPage(){
    $data = json_decode($this->post['data']);
    $page = new \Jazzee\Entity\Page();
    $this->genericPage($page, $data);
    $this->layout = 'blank';
    $applicant = new \Jazzee\Entity\Applicant();
    $applicant->setFirstName('John');
    $applicant->setLastName('Smith');
    $applicant->setMiddleName('T');
    $applicant->setSuffix('Jr.');
    $applicant->setEmail('jtSmith@example.com');
    $ap = new \Jazzee\Entity\ApplicationPage();
    $ap->setPage($page);
    $ap->setApplication($this->_application);
    $ap->getJazzeePage()->setController($this);
    $ap->getJazzeePage()->setApplicant($applicant);
    $this->setVar('page', $ap);
    $this->setVar('applicant', $applicant);
  }
  
  /**
   * Create a generic page to use in a preview
   * @param \Jazzee\Entity\Page $page
   * @param stdClass $data
   */
  protected function genericPage(\Jazzee\Entity\Page $page, \stdClass $data){
    $page->tempId();
    $page->notGlobal();
    $page->setType($this->_em->getRepository('\Jazzee\Entity\PageType')->find($data->classId));
    //create a temporary application page so we can access the JazzeePage and do setup
    if($data->status == 'new'){
      $ap = new \Jazzee\Entity\ApplicationPage();
      $ap->setPage($page);
      $ap->getJazzeePage()->setController($this);
      $ap->getJazzeePage()->setupNewPage();
      unset($ap);
      //give any created elements a temporary id so they will display in the form
      foreach($page->getElements() as $element){
        $element->tempId();
        foreach($element->getListItems() as $item) $item->tempId();
      }
    }
    $page->setTitle($data->title);
    $page->setMin(empty($data->min)?null:$data->min);
    $page->setMax(empty($data->max)?null:$data->max);
    if($data->isRequired) $page->required(); else $page->optional();
    if($data->answerStatusDisplay) $page->showAnswerStatus(); else $page->hideAnswerStatus();
    $page->setInstructions(empty($data->instructions)?null:$data->instructions);
    $page->setLeadingText(empty($data->leadingText)?null:$data->leadingText);
    $page->setTrailingText(empty($data->trailingText)?null:$data->trailingText);
    
    foreach($data->variables as $v){
      $page->setVar($v->name, $v->value);
    }
    foreach($data->elements as $obj){
      $element = new \Jazzee\Entity\Element;
      $this->genericElement($element, $obj);
      $page->addElement($element);
    }
    foreach($data->children as $obj){
      $childPage = new \Jazzee\Entity\Page();
      $this->genericPage($childPage, $obj);
      $page->addChild($childPage);
    }
    $this->_em->clear();
  }
  
  /**
   * Crate a generic element to use in previewing a page
   * @param \Jazzee\Entity\Element $element that we are workign with
   * @param stdClass $e
   */
  protected function genericElement(\Jazzee\Entity\Element $element, \stdClass $e){
    $element->tempId();
    $element->setType($this->_em->getRepository('\Jazzee\Entity\ElementType')->find($e->classId));
    $element->setTitle($e->title);
    $element->setTitle($e->title);
    $element->setFormat(empty($e->format)?null:$e->format);
    $element->setInstructions(empty($e->instructions)?null:$e->instructions);
    $element->setDefaultValue(empty($e->defaultValue)?null:$e->defaultValue);
    if($e->isRequired) $element->required(); else $element->optional();
    $element->setMin(empty($e->min)?null:$e->min);
    $element->setMax(empty($e->max)?null:$e->max);
    foreach($e->list as $i){
      $item = new \Jazzee\Entity\ElementListItem();
      $item->tempId();
      $element->addItem($item);
      $item->setValue($i->value);
      if($item->isActive()) $item->activate(); else $item->deActivate();
    }
  }
  
  /**
   * De-namespace a class name
   * 
   * replace the slashes in namespaced class names with dashes
   * @param string $class
   */
  protected function getClassName($class){
    return str_replace('\\', '', $class);
  }
  
  /**
   * Fake get action path so \Jazzee\Interfaces\Pages have somethign to call
   * 
   */
  public function getActionPath(){
    return $this->path('');
  }
}