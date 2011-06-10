<?php
/**
 * Manage Users
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage manage
 */
class ManageUsersController extends \Jazzee\AdminController {
  const MENU = 'Manage';
  const TITLE = 'Users';
  const PATH = 'manage/users';
  
  const ACTION_INDEX = 'Find User';
  const ACTION_EDIT = 'Edit User';
  const ACTION_NEW = 'New User';
  
  /**
   * Search for a user to modify
   */
  public function actionIndex(){
    $form = new \Foundation\Form();
    $form->setAction($this->path("manage/users/index"));
    $field = $form->newField();
    $field->setLegend('Search Users');
    $element = $field->newElement('TextInput','firstName');
    $element->setLabel('First Name');

    $element = $field->newElement('TextInput','lastName');
    $element->setLabel('Last Name');
    
    $form->newButton('submit', 'Search');
    
    $results = array();  //array of all the users who match the search
    if($input = $form->processInput($this->post)){
      $results = $this->_em->getRepository('\Jazzee\Entity\User')->findByName('%' . $input->get('firstName') . '%', '%' . $input->get('lastName') . '%');
    }
    $this->setVar('results', $results);
    $this->setVar('form', $form);
  }
  
  /**
   * Edit a user
   * @param integer $userID
   */
   public function actionEdit($userID){ 
    if($user = $this->_em->getRepository('\Jazzee\Entity\User')->find($userID)){
      $form = new \Foundation\Form();
      
      $form->setAction($this->path("manage/users/edit/{$userID}"));
      $field = $form->newField();
      $field->setLegend('Edit ' . $user->getFirstName() . ' ' . $user->getLastName());
      
      $element = $field->newElement('CheckboxList','roles');
      $element->setLabel('Global Roles');
      foreach($this->_em->getRepository('\Jazzee\Entity\Role')->findByIsGlobal(true) as $role){
        $element->newItem($role->getId(), $role->getName());
      }
      $values = array();
      foreach($user->getRoles() as $role){
        $values[] = $role->getId();
      }
      $element->setValue($values);
      $form->newButton('submit', 'Save Changes');
      $this->setVar('form', $form);  
      if($input = $form->processInput($this->post)){
        //clear out all current global roles
        foreach($user->getRoles() as $role)if($role->isGlobal()) $user->getRoles()->removeElement($role);            
        
        foreach($input->get('roles') as $roleID){
          $role = $this->_em->getRepository('\Jazzee\Entity\Role')->find($roleID);
          $user->addRole($role);
        }
        $this->_em->persist($user);
        
        $this->addMessage('success', "Changes Saved Successfully");
        $this->redirectPath('manage/users');
      }
    } else {
      $this->addMessage('error', "Error: User #{$userID} does not exist.");
    }
  }
   
  /**
   * Create a new user
   */
   public function actionNew(){
    $form = new \Foundation\Form();
    $form->setAction($this->path('manage/users/new'));
    $field = $form->newField();
    $field->setLegend('New User');
    
    $element = $field->newElement('TextInput','eduPersonPrincipalName');
    $element->setLabel('eduPersonPrincipalName Value');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
 
    $form->newButton('submit', 'Add User');
    $this->setVar('form', $form);  
    if($input = $form->processInput($this->post)){
      $user = new \Jazzee\Entity\User();
      $user->setEduPersonPrincipalName($input->get('eduPersonPrincipalName'));
      $this->_em->persist($user);
      $this->addMessage('success', "New User Added");
      $this->redirectPath('manage/users');
      
    }
  }
}
?>