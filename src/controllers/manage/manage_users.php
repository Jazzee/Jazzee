<?php

/**
 * Manage Users
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ManageUsersController extends \Jazzee\AdminController
{

  const MENU = 'Manage';
  const TITLE = 'Users';
  const PATH = 'manage/users';
  const ACTION_INDEX = 'Find User';
  const ACTION_EDIT = 'Edit User';
  const ACTION_REMOVE = 'Remove User';
  const ACTION_REFRESHUSER = 'Refresh User Directory Information';
  const ACTION_RESETAPIKEY = 'Reset API Key';
  const ACTION_NEW = 'New User';
  const REQUIRE_APPLICATION = false;

  /**
   * Search for a user to modify
   */
  public function actionIndex()
  {
    $directory = $this->getAdminDirectory();
    $form = new \Foundation\Form();
    $field = $form->newField();
    $form->setAction($this->path("manage/users/index"));
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
    $this->setVar('users', $this->_em->getRepository('\Jazzee\Entity\User')->findBy(array('isActive' => true), array('lastName' => 'asc', 'firstName' => 'asc')));
    $this->setVar('roles', $this->_em->getRepository('\Jazzee\Entity\Role')->findByIsGlobal(true));
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
      $form->setAction($this->path("manage/users/edit/{$userID}"));
      $field = $form->newField();
      $field->setLegend('Edit ' . $user->getFirstName() . ' ' . $user->getLastName());

      $element = $field->newElement('CheckboxList', 'roles');
      $element->setLabel('Global Roles');
      foreach ($this->_em->getRepository('\Jazzee\Entity\Role')->findByIsGlobal(true) as $role) {
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
        //clear out all current global roles
        foreach ($user->getRoles() as $role) {
          if ($role->isGlobal()) {
            $user->getRoles()->removeElement($role);
          }
        }
        if($input->get('roles')){
          foreach ($input->get('roles') as $roleID) {
            $role = $this->_em->getRepository('\Jazzee\Entity\Role')->find($roleID);
            $user->addRole($role);
          }
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
   * Remove a user
   * @param integer $userID
   */
  public function actionRemove($userID)
  {
    if ($user = $this->_em->getRepository('\Jazzee\Entity\User')->find($userID)) {
      $user->deactivate();
      $this->_em->persist($user);
      $this->addMessage('success', "User Removed");
      $this->redirectPath('manage/users');
    } else {
      $this->addMessage('error', "Error: User #{$userID} does not exist.");
    }
  }

  /**
   * Refresh a user
   * Query the directory and refresh a users information
   * @param integer $userID
   */
  public function actionRefreshUser($userID)
  {
    if ($user = $this->_em->getRepository('\Jazzee\Entity\User')->find($userID)) {
      $directory = $this->getAdminDirectory();
      $result = $directory->findByUniqueName($user->getUniqueName());
      if (!isset($result[0])) {
        $this->addMessage('error', "Unable to find entry in directory");
        $this->redirectPath('manage/users');
      }
      $user->setFirstName($result[0]['firstName']);
      $user->setLastName($result[0]['lastName']);
      $user->setEmail($result[0]['emailAddress']);
      $this->_em->persist($user);
      $this->addMessage('success', "User Refreshed");
      $this->redirectPath('manage/users');
    } else {
      $this->addMessage('error', "Error: User #{$userID} does not exist.");
    }
  }

  /**
   * Setup API Key
   * View and reset a users API Key
   * @param integer $userID
   */
  public function actionResetApiKey($userID)
  {
    if ($user = $this->_em->getRepository('\Jazzee\Entity\User')->find($userID)) {
      $user->generateApiKey();
      $this->_em->persist($user);
      $this->addMessage('success', "API Key Reset");
      $this->redirectPath('manage/users');
    } else {
      $this->addMessage('error', "Error: User #{$userID} does not exist.");
    }
  }

  /**
   * Add a user
   * Add new user
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
        $this->redirectPath('manage/users');
      }
      $user = new \Jazzee\Entity\User();
      $user->setUniqueName($result[0]['userName']);
      $user->setFirstName($result[0]['firstName']);
      $user->setLastName($result[0]['lastName']);
      $user->setEmail($result[0]['emailAddress']);
      $this->_em->persist($user);
      $this->_em->flush(); //flush early to get the ID
      $this->addMessage('success', "New User Account Created");
    }
    $this->redirectPath('manage/users/edit/' . $user->getId());
  }

}