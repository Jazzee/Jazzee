<?php

/**
 * Setup Program Users
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class SetupUsersController extends \Jazzee\AdminController
{

  const MENU = 'Setup';
  const TITLE = 'Program Users';
  const PATH = 'setup/users';
  const ACTION_INDEX = 'View Users and Search Directory';
  const ACTION_NEW = 'Add User to Program';
  const ACTION_EDIT = 'Grant Permissions';
  const ACTION_REMOVE = 'Remove User from program';
  const REQUIRE_APPLICATION = false;

  /**
   * List users in the programa and earch for new users
   */
  public function actionIndex()
  {
    $directory = $this->getAdminDirectory();
    $form = new \Foundation\Form();
    $field = $form->newField();
    $form->setAction($this->path("setup/users/index"));
    $form->setCSRFToken($this->getCSRFToken());
    $field->setLegend('Find Users');
    $element = $field->newElement('TextInput', 'firstName');
    $element->setLabel('First Name');

    $element = $field->newElement('TextInput', 'lastName');
    $element->setLabel('Last Name');

    $form->newButton('submit', 'Search');

    $results = array();  //array of all the users who match the search
    if ($input = $form->processInput($this->post)) {
      $results = $directory->search($input->get('firstName'), $input->get('lastName'));
    }

    $this->setVar('results', $results);
    $this->setVar('users', $this->_em->getRepository('\Jazzee\Entity\User')->findByProgram($this->_program));
    $this->setVar('roles', $this->_em->getRepository('\Jazzee\Entity\Role')->findBy(array('program' => $this->_program->getId()), array('name' => 'asc')));
    $this->setVar('form', $form);
  }

  /**
   * Edit a user
   * @param integer $userID
   */
  public function actionEdit($userID)
  {
    if ($user = $this->_em->getRepository('\Jazzee\Entity\User')->find($userID)) {
      $form = new \Foundation\Form();
      $form->setCSRFToken($this->getCSRFToken());
      $form->setAction($this->path('setup/users/edit/' . $userID));
      $field = $form->newField();
      $field->setLegend('Roles for ' . $user->getFirstName() . ' ' . $user->getLastName());

      $element = $field->newElement('CheckboxList', 'roles');
      $element->setLabel('Program Roles');
      foreach ($this->_em->getRepository('\Jazzee\Entity\Role')->findBy(array('program' => $this->_program->getId())) as $role) {
        $element->newItem($role->getId(), $role->getName());
      }
      $values = array();
      foreach ($user->getRoles() as $role) {
        $values[] = $role->getId();
      }
      $element->setValue($values);
      $form->newButton('submit', 'Save Changes');
      $this->setVar('form', $form);
      if ($input = $form->processInput($this->post)) {
        //clear out all current program roles
        foreach ($user->getRoles() as $role) {
          if ($role->getProgram() == $this->_program) {
            $user->getRoles()->removeElement($role);
          }
        }
        $roles = $input->get('roles');
        if (!empty($roles)) {
          foreach ($input->get('roles') as $roleID) {
            if ($role = $this->_em->getRepository('\Jazzee\Entity\Role')->findOneBy(array('id' => $roleID, 'program' => $this->_program->getId()))) {
              $user->addRole($role);
            }
          }
        }
        $this->_em->persist($user);
        $this->addMessage('success', "Changes Saved Successfully");
        $this->redirectPath('setup/users');
      }
    } else {
      $this->addMessage('error', "Error: User #{$userID} does not exist.");
    }
  }

  /**
   * Add a user to the program
   * @param string $uniqueName
   */
  public function actionNew($uniqueName)
  {
    $uniqueName = base64_decode($uniqueName);
    if ($user = $this->_em->getRepository('\Jazzee\Entity\User')->findOneBy(array('uniqueName' => $uniqueName))) {
      if (!$user->isActive()) {
        $user->activate();
        $this->_em->persist($user);
        $this->addMessage('success', "User Account Activated");
      }
    } else {
      $directory = $this->getAdminDirectory();
      $result = $directory->findByUniqueName($uniqueName);
      if (!isset($result[0])) {
        $this->addMessage('error', "Unable to find entry in directory");
        $this->redirectPath('setup/users');
      }
      $user = new \Jazzee\Entity\User();
      $user->setUniqueName($result[0]['userName']);
      $user->setFirstName($result[0]['firstName']);
      $user->setLastName($result[0]['lastName']);
      $user->setEmail($result[0]['emailAddress']);
      $this->_em->persist($user);
      $this->_em->flush();
    }
    $this->redirectPath('setup/users/edit/' . $user->getId());
  }

  /**
   * Remove a user from the program
   *
   * just remove all the program roles
   * @param integer $userID
   */
  public function actionRemove($userID)
  {
    if ($user = $this->_em->getRepository('\Jazzee\Entity\User')->find($userID)) {
      //clear out all current program roles
      foreach ($user->getRoles() as $role) {
        if ($role->getProgram() == $this->_program) {
          $user->getRoles()->removeElement($role);
        }
      }
      $this->_em->persist($user);
      $this->addMessage('success', "User removed from program successfuly");
      $this->redirectPath('setup/users');
    } else {
      $this->addMessage('error', "Error: User #{$userID} does not exist.");
    }
  }

}