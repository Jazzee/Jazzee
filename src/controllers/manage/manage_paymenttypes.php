<?php
/**
 * Manage Payment types
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
class ManagePaymenttypesController extends \Jazzee\AdminController {
  const MENU = 'Manage';
  const TITLE = 'Payment Types';
  const PATH = 'manage/paymenttypes';
  
  /**
   * List all the active PaymentTypes and find any new classes on the file system
   */
  public function actionIndex(){
    $this->setVar('paymentTypes', Doctrine::getTable('PaymentType')->findAll(Doctrine::HYDRATE_ARRAY));
  }
  
  /**
   * Edit a payment type
   * @param integer $paymentTypeID
   */
   public function actionEdit($paymentTypeID){ 
    if($paymentType = Doctrine::getTable('PaymentType')->find($paymentTypeID)){
      $className = $paymentType->class;
      $form = $className::setupForm($paymentType);
      $form->action = $this->path("manage/paymenttypes/edit/{$paymentTypeID}");
      $form->newButton('submit', 'Save Changes');
      $this->setVar('form', $form);
      if($input = $form->processInput($this->post)){
        $className::setup($paymentType,$input);
        try {
          $paymentType->save();
          $this->messages->write('success', "Payment Type Saved Successfully");
          $this->redirect($this->path("manage/paymenttypes"));
          $this->afterAction();
          exit(); 
        }
        catch (Doctrine_Validator_Exception $e){
          $records = $e->getInvalidRecords();
          $errors = $records[0]->getErrorStack();
          if($errors->contains('class')){
            if(in_array('unique', $errors->get('class'))){
              $this->messages->write('error', "Payment Type with class {$className} already exists.");
              return;
            }
          }
          if($errors->contains('name')){
            if(in_array('unique', $errors->get('name'))){
              $this->messages->write('error', "Payment Type with name {$input->name} already exists.");
              return;
            }
          }
          throw new Jazzee_Exception($e->getMessage(),E_USER_ERROR,'There was a problem creating a new payment type.');
        }
      }
    } else {
      $this->messages->write('error', "Payment Type #{$paymentTypeID} does not exist.");
    }
  }
   
  /**
   * Create a new paymentType
   */
  public function actionNew($className = false){
    if($className AND class_exists($className)){
      $form = $className::setupForm();
      $form->action = $this->path("manage/paymenttypes/new/{$className}");
      $form->newButton('submit', 'Save');
      if($input = $form->processInput($this->post)){
        $paymentType = new PaymentType;
        $className::setup($paymentType,$input);
        try {
          $paymentType->save();
          $this->messages->write('success', "Payment Type Saved Successfully");
          $this->redirect($this->path("manage/paymenttypes"));
          $this->afterAction();
          exit(); 
        }
        catch (Doctrine_Validator_Exception $e){
          $records = $e->getInvalidRecords();
          $errors = $records[0]->getErrorStack();
          if($errors->contains('class')){
            if(in_array('unique', $errors->get('class'))){
              $this->messages->write('error', "Payment Type with class {$className} already exists.");
              return;
            }
          }
          if($errors->contains('name')){
            if(in_array('unique', $errors->get('name'))){
              $this->messages->write('error', "Payment Type with name {$input->name} already exists.");
              return;
            }
          }
          throw new Jazzee_Exception($e->getMessage(),E_USER_ERROR,'There was a problem creating a new payment type.');
        }
      }
    } else if(!empty($this->post['class'])){
      $className = $this->post['class'];
      if(!class_exists($className)){
        throw new Jazzee_Exception("{$className} is not a valid class.",E_USER_ERROR,'Unable to create a payment of that type.');
      }
      $form = $className::setupForm();
      $form->action = $this->path("manage/paymenttypes/new/{$className}");
      $form->newButton('submit', 'Save');
    } else {
      $pageTypes = Doctrine::getTable('PaymentType')->findAll(Doctrine::HYDRATE_ARRAY);
      $existingClasses = array();
      foreach($pageTypes as $arr){
        $existingClasses[] = $arr['class'];
      }
      $classes = array();
      $paths = array( //all the places an ApplyPage class can be found
        APP_ROOT . '/models/payment_types'
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
              if(is_subclass_of($class, 'ApplyPayment') and !in_array($class, $existingClasses)){
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
        $form->action = $this->path("manage/paymenttypes/new/");
        $field = $form->newField(array('legend'=>"New Payment Types"));
        $element = $field->newElement('SelectList', 'class');
        $element->label = 'Payment Class';
        $element->addValidator('NotEmpty');
        foreach($classes as $class){
          $element->addItem($class, $class);
        }
        $form->newButton('submit', 'Add Payment Type');
      }
    }
    $this->setVar('form', $form);
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Manage Payment Types';
    $auth->addAction('index', new ActionAuth('View Payment Types'));
    $auth->addAction('edit', new ActionAuth('Edit Payment Type'));
    $auth->addAction('new', new ActionAuth('Create New Payment Type'));
    return $auth;
  }
}
?>