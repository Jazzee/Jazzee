<?php

/**
 * Manage Global Roles
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ManageRolesController extends \Jazzee\AdminController
{

  const MENU = 'Manage';
  const TITLE = 'Roles';
  const PATH = 'manage/roles';
  const ACTION_INDEX = 'View Roles';
  const ACTION_EDIT = 'Edit Role';
  const ACTION_NEW = 'New Role';
  const ACTION_APPLYTEMPLATE = 'Apply a role to program roles';
  const ACTION_COPY = 'Copy a role';
  const REQUIRE_APPLICATION = false;

  /**
   * Add the required JS
   */
  protected function setUp()
  {
    parent::setUp();
    $this->addScript($this->path('resource/scripts/controllers/manage_roles.controller.js'));
  }

  /**
   * List all the Roles
   */
  public function actionIndex()
  {
    $this->setVar('roles', $this->_em->getRepository('\Jazzee\Entity\Role')->findByIsGlobal(true));
  }

  /**
   * Edit a role
   * @param integer $roleID
   */
  public function actionEdit($roleID)
  {
    if ($role = $this->_em->getRepository('\Jazzee\Entity\Role')->findOneBy(array('id' => $roleID, 'isGlobal' => true))) {
      $form = new \Foundation\Form;
      $form->setCSRFToken($this->getCSRFToken());
      $form->setAction($this->path('manage/roles/edit/' . $role->getId()));
      $field = $form->newField();
      $field->setLegend('Edit ' . $role->getName() . ' role');
      $element = $field->newElement('TextInput', 'name');
      $element->setLabel('Role Name');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->addFilter(new \Foundation\Form\Filter\Safe($element));
      $element->setValue($role->getName());
      $menus = $this->getControllerActions();
      ksort($menus);
      foreach ($menus as $menu => $list) {
        foreach ($list as $controller) {
          $element = $field->newElement('CheckboxList', $controller['name']);
          $element->setLabel($menu . ' ' . $controller['title'] . ' actions');
          foreach ($controller['actions'] as $actionName => $actionTitle) {
            $element->newItem($actionName, $actionTitle);
          }
          $values = array();
          foreach ($role->getActions() as $action) {
            if ($action->getController() == $controller['name']) {
              $values[] = $action->getAction();
            }
          }
          $element->setValue($values);
        }
      }
      $form->newButton('submit', 'Edit Role');
      $this->setVar('form', $form);
      if ($input = $form->processInput($this->post)) {
        $role->setName($input->get('name'));
        foreach ($role->getActions() as $action) {
          $this->_em->remove($action);
          $role->getActions()->removeElement($action);
        }

        foreach ($menus as $menu => $list) {
          foreach ($list as $controller) {
            $actions = $input->get($controller['name']);
            if (!empty($actions)) {
              foreach ($actions as $actionName) {
                $action = new \Jazzee\Entity\RoleAction;
                $action->setController($controller['name']);
                $action->setAction($actionName);
                $action->setRole($role);
                $this->_em->persist($action);
              }
            }
          }
        }
        $this->_em->persist($role);
        $this->addMessage('success', "Role Saved Successfully");
        unset($this->_store->AdminControllerGetNavigation);
        $this->redirectPath('manage/roles');
      }
    } else {
      $this->addMessage('error', "Error: Role #{$roleID} does not exist.");
    }
  }

  /**
   * Copy a role
   * @param integer $oldRoleID
   */
  public function actionCopy($oldRoleID)
  {
    if ($oldRole = $this->_em->getRepository('\Jazzee\Entity\Role')->findOneBy(array('id' => $oldRoleID, 'isGlobal' => true))) {
      $form = new \Foundation\Form;
      $form->setCSRFToken($this->getCSRFToken());
      $form->setAction($this->path('manage/roles/copy/' . $oldRole->getId()));
      $field = $form->newField();
      $field->setLegend('COpy ' . $oldRole->getName() . ' role');
      $element = $field->newElement('TextInput', 'name');
      $element->setLabel('New Role Name');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->addFilter(new \Foundation\Form\Filter\Safe($element));
      $element->setValue($oldRole->getName());
      $menus = $this->getControllerActions();
      ksort($menus);
      foreach ($menus as $menu => $list) {
        foreach ($list as $controller) {
          $element = $field->newElement('CheckboxList', $controller['name']);
          $element->setLabel($menu . ' ' . $controller['title'] . ' actions');
          foreach ($controller['actions'] as $actionName => $actionTitle) {
            $element->newItem($actionName, $actionTitle);
          }
          $values = array();
          foreach ($oldRole->getActions() as $action) {
            if ($action->getController() == $controller['name']) {
              $values[] = $action->getAction();
            }
          }
          $element->setValue($values);
        }
      }
      $form->newButton('submit', 'Copy Role');
      $this->setVar('form', $form);
      if ($input = $form->processInput($this->post)) {
        $newRole = new \Jazzee\Entity\Role;
        $newRole->makeGlobal();
        $newRole->setName($input->get('name'));
        foreach ($menus as $menu => $list) {
          foreach ($list as $controller) {
            $actions = $input->get($controller['name']);
            if (!empty($actions)) {
              foreach ($actions as $actionName) {
                $action = new \Jazzee\Entity\RoleAction;
                $action->setController($controller['name']);
                $action->setAction($actionName);
                $action->setRole($newRole);
                $this->_em->persist($action);
              }
            }
          }
        }
        $this->_em->persist($newRole);
        $this->addMessage('success', "Role Copied Successfully");
        $this->redirectPath('manage/roles');
      }
    } else {
      $this->addMessage('error', "Error: Role #{$oldRoleID} does not exist.");
    }
  }

  /**
   * Create a new role
   */
  public function actionNew()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("manage/roles/new"));
    $field = $form->newField();
    $field->setLegend('New Global Role');
    $element = $field->newElement('TextInput', 'name');
    $element->setLabel('Role Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    $form->newButton('submit', 'Add Role');
    $this->setVar('form', $form);
    if ($input = $form->processInput($this->post)) {
      $role = new \Jazzee\Entity\Role();
      $role->makeGlobal();
      $role->setName($input->get('name'));
      $this->_em->persist($role);
      $this->addMessage('success', "Role Saved Successfully");
      $this->redirectPath('manage/roles');
    }
  }

  /**
   * Apply a role template
   * Use a global role as a template to apply accross multipe programs
   * @param integer $roleId
   */
  public function actionApplyTemplate($roleId)
  {
    if ($role = $this->_em->getRepository('\Jazzee\Entity\Role')->findOneBy(array('id' => $roleId, 'isGlobal' => true))) {
      $form = new \Foundation\Form;
      $form->setCSRFToken($this->getCSRFToken());
      $form->setAction($this->path('manage/roles/applytemplate/' . $role->getId()));
      $field = $form->newField();
      $field->setLegend('Apply ' . $role->getName() . ' to program roles');
      $programs = $this->_em->getRepository('\Jazzee\Entity\Program')->findBy(array('isExpired' => false), array('name' => 'ASC'));
      $userPrograms = $this->_user->getPrograms();
      //keep a list to use if we post data
      $list = array();
      foreach ($programs as $program) {
        $list['program' . $program->getId()] = array();
        $element = $field->newElement('CheckboxList', 'program' . $program->getId());
        $element->setLabel($program->getName() . ' roles');
        if ($this->checkIsAllowed('admin_changeprogram', 'anyProgram') or in_array($program->getId(), $userPrograms)) {
          $programRoles = $this->_em->getRepository('\Jazzee\Entity\Role')->findBy(array('isGlobal' => false, 'program' => $program->getId()), array('name' => 'ASC'));
          foreach ($programRoles as $programRole) {
            $element->newItem('programrole' . $programRole->getId(), $programRole->getName());
            $list['program' . $program->getId()]['programrole' . $programRole->getId()] = $programRole->getId();
          }
        }
      }
      $form->newButton('submit', 'Apply Templates');
      $this->setVar('form', $form);
      if ($input = $form->processInput($this->post)) {
        foreach ($list as $programElementId => $programArr) {
          foreach ($programArr as $programRoleElementId => $roleId) {
            $setRoles = $input->get($programElementId);
            if (!is_null($setRoles) and in_array($programRoleElementId, $setRoles)) {
              $programRole = $this->_em->getRepository('\Jazzee\Entity\Role')->findOneBy(array('id' => $roleId, 'isGlobal' => false, 'program' => substr($programElementId, 7)));
              if (!$programRole) {
                throw new \Jazzee\Exception('Bad role or program');
              }
              foreach ($programRole->getActions() as $action) {
                $this->_em->remove($action);
                $programRole->getActions()->removeElement($action);
              }
              foreach ($role->getActions() as $globalAction) {
                $programAction = new \Jazzee\Entity\RoleAction;
                $programAction->setController($globalAction->getController());
                $programAction->setAction($globalAction->getAction());
                $programAction->setRole($programRole);
                $this->_em->persist($programAction);
              }
            }
          }
        }
        $this->addMessage('success', "Template Applied Successfully");
        $this->redirectPath('manage/roles');
      }
    } else {
      $this->addMessage('error', "Error: Role #{$roleId} does not exist.");
    }
  }

  /**
   * Get All of the possible controllers and actions
   * @return array of ControllerAuths
   */
  protected function getControllerActions()
  {
    $controllers = array();
    foreach ($this->listControllers() as $controller) {
      \Foundation\VC\Config::includeController($controller);
      $class = \Foundation\VC\Config::getControllerClassName($controller);
      $arr = array('name' => $controller, 'title' => $class::TITLE, 'actions' => array());
      foreach (get_class_methods($class) as $method) {
        if (substr($method, 0, 6) == 'action') {
          $constant = 'ACTION_' . strtoupper(substr($method, 6));
          if (defined("{$class}::{$constant}")) {
            $arr['actions'][strtolower(substr($method, 6))] = constant("{$class}::{$constant}");
          }
        }
      }
      if (!empty($arr['actions'])) {
        $controllers[$class::MENU][] = $arr;
      }
    }

    return $controllers;
  }

}