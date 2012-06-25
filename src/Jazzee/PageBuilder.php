<?php
namespace Jazzee;
/**
 * PageBuilder
 * 
 * The page builder abstracts common functionality between application pages and global pages
 * @package jazzee
 * @subpackage admin
 */
abstract class PageBuilder extends AdminController{
  
  /**
   * Add the required JS
   */
  public function setUp(){
    //everything is displayed over json
    $this->layout = 'json';
    
    $this->addScript($this->path('resource/foundation/scripts/form.js'));
    $this->addScript($this->path('resource/foundation/scripts/jquery.wysiwyg.js'));
    
    $this->addCss($this->path('resource/foundation/styles/jquery.wysiwyg.css'));
    
    $types = $this->_em->getRepository('\Jazzee\Entity\PageType')->findAll();
    $scripts = array();
    $scripts[] = $this->path('resource/scripts/page_types/JazzeePage.js');
    foreach($types as $type){
      $class = $type->getClass();
      $scripts[] = $this->path($class::pageBuilderScriptPath());
    }
    $scripts = array_unique($scripts);
    foreach($scripts as $path) $this->addScript($path);
    
    $this->addScript($this->path('resource/scripts/element_types/JazzeeElement.js'));
    
    $types = $this->_em->getRepository('\Jazzee\Entity\ElementType')->findAll();
    $scripts = array();
    $scripts[] = $this->path(\Jazzee\Interfaces\Element::PAGEBUILDER_SCRIPT);
    $scripts[] = $this->path('resource/scripts/element_types/List.js');
    $scripts[] = $this->path('resource/scripts/element_types/FileInput.js');
    foreach($types as $type){
      $class = $type->getClass();
      $scripts[] = $this->path($class::PAGEBUILDER_SCRIPT);
    }
    $scripts = array_unique($scripts);
    foreach($scripts as $path) $this->addScript($path);
    
    $this->addScript($this->path('resource/scripts/classes/PageBuilder.class.js'));
    $this->addCss($this->path('resource/styles/pages.css'));

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
      'trailingText' => $page->getTrailingText()
    );
    
    //now that we have completed the general setup replace $applicationPage with $page
    if($page instanceof \Jazzee\Entity\ApplicationPage){
      $arr['weight'] = $page->getWeight();
      $arr['applicationPageId'] = $page->getId();
      $arr['kind'] = $page->getKind();
      $page = $page->getPage();
      //for global pages also pass the global page info for reference
      if($page->isGlobal()){
        $arr['globalPage'] = array(
          'title' => $page->getTitle(),
          'min' => is_null($page->getMin())?0:$page->getMin(),
          'max' => is_null($page->getMax())?0:$page->getMax(),
          'isRequired' => (int)$page->isRequired(),
          'answerStatusDisplay' => $page->answerStatusDisplay()?1:0,
          'instructions' => $page->getInstructions(),
          'leadingText' => $page->getLeadingText(),
          'trailingText' => $page->getTrailingText()
        );
      }
    } 
    $arr['id'] = $page->getId();
    $arr['uuid'] = $page->getUuid();
    $arr['typeClass'] = $this->getClassName($page->getType()->getClass());
    $arr['typeName'] = $this->getClassName($page->getType()->getName());
    $arr['typeId'] = $page->getType()->getId();
    $arr['isGlobal'] = $page->isGlobal()?1:0;
    $arr['hasAnswers'] = $this->_em->getRepository('\Jazzee\Entity\Page')->hasAnswers($page);
    $arr['hasCycleAnswers'] = $this->_em->getRepository('\Jazzee\Entity\Page')->hasCycleAnswers($page, $this->_cycle);
    $arr['interfaces'] = array_values(class_implements($page->getType()->getClass()));
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
      $e['typeClass'] = $this->getClassName($element->getType()->getClass());
      $e['typeName'] = $this->getClassName($element->getType()->getName());
      $e['typeId'] = $element->getType()->getId();
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
        'typeClass' => $this->getClassName($pageTypes[$id]->getClass()),
        'typeName' => $this->getClassName($pageTypes[$id]->getName()),
        'interfaces' => array_values(class_implements($pageTypes[$id]->getClass()))
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
        'typeId' => $id,
        'typeName' => $elementTypes[$id]->getName(),
        'typeClass' => $this->getClassName($elementTypes[$id]->getClass()),
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
    $htmlPurifier = $this->getFilter();
    
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
        $this->addMessage('success',$page->getTitle() . ' page saved.');
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
          $childPage = $page->getChildById($child->id);
          $this->_em->remove($childPage);
          $page->getChildren()->removeElement($childPage);
          $this->addMessage('success',$childPage->getTitle() . ' page deleted.');
        break;
        case 'import':
          $childPage = new \Jazzee\Entity\Page();
          $childPage->setParent($page);
          $childPage->notGlobal();
          $childPage->setType($this->_em->getRepository('\Jazzee\Entity\PageType')->find($child->typeId));
          $childPage->setUuid($child->uuid);
          $this->savePage($childPage, $child);
          break;
        case 'new':
          $childPage = new \Jazzee\Entity\Page();
          $childPage->setParent($page);
          $childPage->notGlobal();
          $childPage->setType($this->_em->getRepository('\Jazzee\Entity\PageType')->find($child->typeId));
          $this->savePage($childPage, $child);
          break;
        default:
          $childPage = $page->getChildById($child->id);
          $this->savePage($childPage, $child);
        break;
      }
      unset($childPage);
    }
    $this->addMessage('success',$page->getTitle() . ' page saved.');
  }
  
  /**
   * Update all of the elements on a page with an array of elements passed in
   * @param \Jazzee\Entity\Page $page
   * @param array $elements
   */
  protected function savePageElements(\Jazzee\Entity\Page $page, array $elements){
    $htmlPurifier = $this->getFilter();
    foreach($elements as $e){
      switch($e->status){
        case 'delete':
          //don't try and delete temporary elements
          if($element = $page->getElementByID($e->id)){
            if($this->_em->getRepository('\Jazzee\Entity\Page')->hasAnswers($page)){
              $this->setLayoutVar('status', 'error');
              $this->addMessage('error',$element->getTitle() . '  could not be deleted becuase it has applicant information associated with it.');
            } else {
              $this->_em->remove($element);
              $page->getElements()->remove($element->getId());
            }
          }
          break;
        case 'new':
            $element = new \Jazzee\Entity\Element();
            $page->addElement($element);
            $element->setType($this->_em->getRepository('\Jazzee\Entity\ElementType')->find($e->typeId));
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
    $applicant->setApplication($this->_application);
    $applicationPage = new \Jazzee\Entity\ApplicationPage();
    $applicationPage->setPage($page);
    $applicationPage->setApplication($this->_application);
    $applicationPage->getJazzeePage()->setController($this);
    $applicationPage->getJazzeePage()->setApplicant($applicant);
    $this->setVar('page', $applicationPage);
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
    $page->setType($this->_em->getRepository('\Jazzee\Entity\PageType')->find($data->typeId));
    //create a temporary application page so we can access the JazzeePage and do setup
    if($data->status == 'new'){
      $applicationPage = new \Jazzee\Entity\ApplicationPage();
      $applicationPage->setPage($page);
      $applicationPage->getJazzeePage()->setController($this);
      $applicationPage->getJazzeePage()->setupNewPage();
      unset($applicationPage);
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
   * @param stdClass $obj
   */
  protected function genericElement(\Jazzee\Entity\Element $element, \stdClass $obj){
    $element->tempId();
    $element->setType($this->_em->getRepository('\Jazzee\Entity\ElementType')->find($obj->typeId));
    $element->setTitle($obj->title);
    $element->setTitle($obj->title);
    $element->setFormat(empty($obj->format)?null:$obj->format);
    $element->setInstructions(empty($obj->instructions)?null:$obj->instructions);
    $element->setDefaultValue(empty($obj->defaultValue)?null:$obj->defaultValue);
    if($obj->isRequired) $element->required(); else $element->optional();
    $element->setMin(empty($obj->min)?null:$obj->min);
    $element->setMax(empty($obj->max)?null:$obj->max);
    foreach($obj->list as $i){
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
  
  protected function getFilter(){
    $cachePath = $this->getVarPath() . '/tmp/htmlpurifiercache';
    if (!is_dir($cachePath)) {
      mkdir($cachePath, 0755, true);
    }
    //call the bootstrap class so that we get the constant definitions
    $bsBootstrap = new \HTMLPurifier_Bootstrap();
    unset($bsBootstrap);
    // set up configuration
    $config = \HTMLPurifier_Config::createDefault();
    $config->set('HTML.DefinitionID', 'JazzeeJazzeeConfig');
    $config->set('HTML.DefinitionRev', 1); // increment when configuration changes
    $config->set('Cache.SerializerPath', $cachePath);

    $purifier = new \HTMLPurifier($config);

    return $purifier;
  }
}