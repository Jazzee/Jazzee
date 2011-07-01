<?php
/**
 * Setup Program Users
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage setup
 */
class SetupUsersController extends \Jazzee\AdminController {
  const MENU = 'Setup';
  const TITLE = 'Program Users';
  const PATH = 'setup/users';
  
  const ACTION_INDEX = 'Search Users';
  const ACTION_PROGRAMROLES = 'Grant Permissions';
  const REQUIRE_APPLICATION = false;
  
  /**
   * Search for a user to modify
   */
  public function actionIndex(){
    $form = new \Foundation\Form();
    $form->setAction($this->path('setup/users'));
    $field = $form->newField();
    $field->setLegend('Search For New');
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
    $this->setVar('users', $this->_em->getRepository('\Jazzee\Entity\User')->findByProgram($this->_program));
    $this->setVar('form', $form);
  }
  
  /**
   * Edit a user
   * @param integer $userID
   */
   public function actionProgramRoles($userID){ 
    if($user = $this->_em->getRepository('\Jazzee\Entity\User')->find($userID)){
      $form = new \Foundation\Form();
      $form->setAction($this->path('setup/users/programRoles/' . $userID));
      $field = $form->newField();
      $field->setLegend('Roles for ' . $user->getFirstName() . ' ' . $user->getLastName());

      $element = $field->newElement('CheckboxList','roles');
      $element->setLabel('Program Roles');
      foreach($this->_em->getRepository('\Jazzee\Entity\Role')->findBy(array('program' => $this->_program->getId())) as $role){
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
        foreach($user->getRoles() as $role)if($role->getProgram() == $this->_program) $user->getRoles()->removeElement($role);            
        
        foreach($input->get('roles') as $roleID){
          $role = $this->_em->getRepository('\Jazzee\Entity\Role')->findOneBy(array('id' => $roleID, 'program' => $this->_program->getId()));
          $user->addRole($role);
        }
        $this->_em->persist($user);
        $this->addMessage('success', "Changes Saved Successfully");
        $this->redirectPath('setup/users');
      }
    } else {
      $this->addMessage('error', "Error: User #{$userID} does not exist.");
    }
  }
}
?>