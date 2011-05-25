<?php
/**
 * Manage Element types
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
class ManageElementtypesController extends ManageController {
  const MENU = 'Manage';
  const TITLE = 'Element Types';
  const PATH = 'manage/elementtypes';
  
  /**
   * List all the active ElementTypes and find any new classes on the file system
   */
  public function actionIndex(){
    $this->setVar('elementTypes', Doctrine::getTable('ElementType')->findAll(Doctrine::HYDRATE_ARRAY));
  }
  
  /**
   * Edit an ElementType
   * @param integer $elementTypeID
   */
   public function actionEdit($elementTypeID){ 
    if($elementType = Doctrine::getTable('ElementType')->find($elementTypeID)){
      $form = new Form;
      
      $form->action = $this->path("manage/elementtypes/edit/{$elementTypeID}");
      $field = $form->newField(array('legend'=>"Edit {$elementType->class} Element Type"));
      $element = $field->newElement('TextInput','name');
      $element->label = 'Name';
      $element->addValidator('NotEmpty');
      $element->value = $elementType->name;
  
      $form->newButton('submit', 'Save Changes');
      $this->setVar('form', $form);  
      if($input = $form->processInput($this->post)){
        $elementType->name = $input->name;
        try {
          $elementType->save();
          $this->messages->write('success', "Element Type Saved Successfully");
          $this->redirect($this->path("manage/elementtypes/"));
          $this->afterAction();
          exit(); 
        }
        catch (Doctrine_Validator_Exception $e){
          $records = $e->getInvalidRecords();
          $errors = $records[0]->getErrorStack();
          if($errors->contains('name')){
            if(in_array('unique', $errors->get('name'))){
              $this->messages->write('error', "ElementType with name {$input->name} already exists.");
              return;
            }
          }
          throw new Jazzee_Exception($e->getMessage(),E_USER_ERROR,'There was a problem creating a new element type.');
        }
      }
    } else {
      $this->messages->write('error', "Error: ElementType #{$elementTypeID} does not exist.");
    }
  }
   
  /**
   * Create a new pagetype
   */
   public function actionNew(){
    $elementTypes = Doctrine::getTable('ElementType')->findAll(Doctrine::HYDRATE_ARRAY);
    $existingClasses = array();
    foreach($elementTypes as $arr){
      $existingClasses[] = $arr['class'];
    }
    $classes = array();
    $paths = array( //all the places an ApplyPage class can be found
      APP_ROOT . '/models/element_types'
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
            if(is_subclass_of($class, 'ApplyElement') and !in_array($class, $existingClasses)){
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
      $form->action = $this->path("manage/elementtypes/new/");
      $field = $form->newField(array('legend'=>"New Element Types"));
      $element = $field->newElement('TextInput','name');
      $element->label = 'Element Name';
      $element->addValidator('NotEmpty');
      
      $element = $field->newElement('SelectList', 'class');
      $element->label = 'Element Class';
      $element->addValidator('NotEmpty');
      foreach($classes as $class){
        $element->addItem($class, $class);
      }
  
      $form->newButton('submit', 'Add Element');
      $this->setVar('form', $form); 
      if($input = $form->processInput($this->post)){
        
        $elementType = new ElementType;
        $elementType->name = $input->name;
        $elementType->class = $input->class;
        try {
          $elementType->save();
          $this->messages->write('success', "Element Type Saved Successfully");
          $this->redirect($this->path("manage/elementtypes"));
          $this->afterAction();
          exit(); 
        }
        catch (Doctrine_Validator_Exception $e){
          $records = $e->getInvalidRecords();
          $errors = $records[0]->getErrorStack();
          if($errors->contains('class')){
            if(in_array('unique', $errors->get('class'))){
              $this->messages->write('error', "ElementType with class {$input->class} already exists.");
              return;
            }
          }
          if($errors->contains('name')){
            if(in_array('unique', $errors->get('name'))){
              $this->messages->write('error', "ElementType with name {$input->name} already exists.");
              return;
            }
          }
          throw new Jazzee_Exception($e->getMessage(),E_USER_ERROR,'There was a problem creating a new element type.');
        }
      }
    }
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Manage Element Types';
    $auth->addAction('index', new ActionAuth('View Element Types'));
    $auth->addAction('edit', new ActionAuth('Edit Element Type'));
    $auth->addAction('new', new ActionAuth('Create Element Page Type'));
    return $auth;
  }
}
?>