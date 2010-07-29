<?php
/**
 * Manage Page types
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage manage
 */
class ManagePagetypesController extends ManageController {
  /**
   * List all the active PageTypes and find any new classes on the file system
   */
  public function actionIndex(){
    $this->setVar('pageTypes', Doctrine::getTable('PageType')->findAll(Doctrine::HYDRATE_ARRAY));
  }
  
  /**
   * Edit a page type
   * @param integer $pageTypeID
   */
   public function actionEdit($pageTypeID){ 
    if($pageType = Doctrine::getTable('PageType')->find($pageTypeID)){
      $form = new Form;
      
      $form->action = $this->path("manage/pagetypes/edit/{$pageTypeID}");
      $field = $form->newField(array('legend'=>"Edit {$pageType->class} Page Type"));
      $element = $field->newElement('TextInput','name');
      $element->label = 'Name';
      $element->addValidator('NotEmpty');
      $element->value = $pageType->name;
  
      $form->newButton('submit', 'Save Changes');
      $this->setVar('form', $form);  
      if($input = $form->processInput($this->post)){
        $pageType->name = $input->name;
        try {
          $pageType->save();
          $this->messages->write('success', "Page Type Saved Successfully");
          $this->redirect($this->path("manage/pagetypes/"));
          $this->afterAction();
          exit(); 
        }
        catch (Doctrine_Validator_Exception $e){
          $records = $e->getInvalidRecords();
          $errors = $records[0]->getErrorStack();
          if($errors->contains('name')){
            if(in_array('unique', $errors->get('name'))){
              $this->messages->write('error', "Pagetype with name {$input->name} already exists.");
              return;
            }
          }
          throw new Jazzee_Exception($e->getMessage(),E_USER_ERROR,'There was a problem creating a new page type.');
        }
      }
    } else {
      $this->messages->write('error', "Error: PageType #{$pageTypeID} does not exist.");
    }
  }
   
  /**
   * Create a new pagetype
   */
   public function actionNew(){
    $pageTypes = Doctrine::getTable('PageType')->findAll(Doctrine::HYDRATE_ARRAY);
    $existingClasses = array();
    foreach($pageTypes as $arr){
      $existingClasses[] = $arr['class'];
    }
    $classes = array();
    $paths = array( //all the places an ApplyPage class can be found
      APP_ROOT . '/models/page_types'
    );
    //whenever a subdirectory is discovered in the array continue traversing the array
    reset ($paths);
    while (list($key, $path) = each ($paths)) {
      $handle = opendir($path);
      while (false !== ($file = readdir($handle))) {
        if($file != "." && $file != ".."){
          if(is_dir($path . $file)){
            $paths[] = $path . $file . '/'; 
          } else {
            $class = substr($file, 0,-10);
            if(is_subclass_of($class, 'ApplyPage') and !in_array($class, $existingClasses)){
              $classes[] = $class;
            }
          }
        }
      }
      closedir($handle);
    }
    //put the types in alphabetical order by class name
    sort($classes);

    //don't display the form if there are no new page types
    if(!empty($classes)){
      $form = new Form;
      $form->action = $this->path("manage/pagetypes/new/");
      $field = $form->newField(array('legend'=>"New Page Types"));
      $element = $field->newElement('TextInput','name');
      $element->label = 'Page Name';
      $element->addValidator('NotEmpty');
      
      $element = $field->newElement('SelectList', 'class');
      $element->label = 'Page Class';
      $element->addValidator('NotEmpty');
      foreach($classes as $class){
        $element->addItem($class, $class);
      }
  
      $form->newButton('submit', 'Add Page');
      $this->setVar('form', $form); 
      if($input = $form->processInput($this->post)){
        
        $pageType = new PageType;
        $pageType->name = $input->name;
        $pageType->class = $input->class;
        try {
          $pageType->save();
          $this->messages->write('success', "Page Type Saved Successfully");
          $this->redirect($this->path("manage/pagetypes"));
          $this->afterAction();
          exit(); 
        }
        catch (Doctrine_Validator_Exception $e){
          $records = $e->getInvalidRecords();
          $errors = $records[0]->getErrorStack();
          if($errors->contains('class')){
            if(in_array('unique', $errors->get('class'))){
              $this->messages->write('error', "Pagetype with class {$input->class} already exists.");
              return;
            }
          }
          if($errors->contains('name')){
            if(in_array('unique', $errors->get('name'))){
              $this->messages->write('error', "Pagetype with name {$input->name} already exists.");
              return;
            }
          }
          throw new Jazzee_Exception($e->getMessage(),E_USER_ERROR,'There was a problem creating a new page type.');
        }
      }
    }
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Manage Page Types';
    $auth->addAction('index', new ActionAuth('View Page Types'));
    $auth->addAction('edit', new ActionAuth('Edit Page Type'));
    $auth->addAction('new', new ActionAuth('Create New Page Type'));
    return $auth;
  }
}
?>