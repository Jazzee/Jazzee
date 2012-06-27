<?php

/**
 * Setup Program Roles
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class SetupRolesController extends \Jazzee\AdminController
{

  const MENU = 'Setup';
  const TITLE = 'Program Roles';
  const PATH = 'setup/roles';
  const ACTION_INDEX = 'View';
  const ACTION_EDIT = 'Edit';
  const ACTION_COPY = 'Copy';
  const ACTION_NEW = 'New';

  /**
   * Add the required JS
   */
  protected function setUp()
  {
    parent::setUp();
    $this->addScript($this->path('resource/scripts/controllers/setup_roles.controller.js'));
  }

  /**
   * List all the Roles
   */
  public function actionIndex()
  {
    $this->setVar('roles', $this->_em->getRepository('\Jazzee\Entity\Role')->findByProgram($this->_program->getId()));
  }

  /**
   * Edit a role
   * @param integer $roleID
   */
  public function actionEdit($roleID)
  {
    if ($role = $this->_em->getRepository('\Jazzee\Entity\Role')->findOneBy(array('id' => $roleID, 'program' => $this->_program->getId()))) {
      $form = new \Foundation\Form;
      $form->setCSRFToken($this->getCSRFToken());
      $form->setAction($this->path('setup/roles/edit/' . $role->getId()));
      $field = $form->newField();
      $field->setLegend('Edit ' . $role->getName() . ' role');
      $element = $field->newElement('TextInput', 'name');
      $element->setLabel('Role Name');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->setValue($role->getName());
      $element->addFilter(new \Foundation\Form\Filter\Safe($element));
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
        $role->notGlobal();
        $role->setProgram($this->_program);
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
        $this->redirectPath('setup/roles');
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
    if ($oldRole = $this->_em->getRepository('\Jazzee\Entity\Role')->findOneBy(array('id' => $oldRoleID, 'program' => $this->_program->getId()))) {
      $form = new \Foundation\Form;
      $form->setCSRFToken($this->getCSRFToken());
      $form->setAction($this->path('setup/roles/copy/' . $oldRole->getId()));
      $field = $form->newField();
      $field->setLegend('Copy ' . $oldRole->getName() . ' role');
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
        $newRole->notGlobal();
        $newRole->setProgram($this->_program);
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
        $this->redirectPath('setup/roles');
      }
    } else {
      $this->addMessage('error', "Error: Role #{$oldRoleID} does not exist.");
    }
  }

  /**
   * Create a new pagetype
   */
  public function actionNew()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('setup/roles/new'));
    $field = $form->newField();
    $field->setLegend('New program role');
    $element = $field->newElement('TextInput', 'name');
    $element->setLabel('Role Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));

    $form->newButton('submit', 'Add Role');
    $this->setVar('form', $form);
    if ($input = $form->processInput($this->post)) {
      $role = new \Jazzee\Entity\Role();
      $role->notGLobal();
      $role->setProgram($this->_program);
      $role->setName($input->get('name'));
      $this->_em->persist($role);
      $this->addMessage('success', "Role Saved Successfully");
      $this->redirectPath('setup/roles');
    }
  }

  /**
   * Get All of the possible controllers and actions
   *
   * only allow the ones the user has access to
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
          $actionName = strtolower(substr($method, 6));
          if ($this->checkIsAllowed($controller, $actionName) AND defined("{$class}::{$constant}")) {
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