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
  const ACTION_GETROLEDISPLAY = 'Set Maximum Display';
  const REQUIRE_APPLICATION = false;

  /**
   * Add the required JS
   */
  protected function setUp()
  {
    parent::setUp();
    $this->addScript($this->path('resource/scripts/classes/Display.class.js'));
    $this->addScript($this->path('resource/scripts/classes/Application.class.js'));
    $this->addScript($this->path('resource/scripts/classes/DisplayManager.class.js'));
    $this->addScript($this->path('resource/scripts/controllers/setup_roles.controller.js'));
    
    $this->addCss($this->path('resource/styles/displaymanager.css'));
    //add all of the JazzeePage scripts for display
    $types = $this->_em->getRepository('\Jazzee\Entity\PageType')->findAll();
    $scripts = array();
    $scripts[] = $this->path('resource/scripts/page_types/JazzeePage.js');
    foreach ($types as $type) {
      $class = $type->getClass();
      $scripts[] = $this->path($class::pageBuilderScriptPath());
    }

    //add all of the Jazzee element scripts for data rendering
    $this->addScript($this->path('resource/scripts/element_types/JazzeeElement.js'));

    $types = $this->_em->getRepository('\Jazzee\Entity\ElementType')->findAll();
    $scripts[] = $this->path(\Jazzee\Interfaces\Element::PAGEBUILDER_SCRIPT);
    $scripts[] = $this->path('resource/scripts/element_types/List.js');
    $scripts[] = $this->path('resource/scripts/element_types/FileInput.js');
    foreach ($types as $type) {
      $class = $type->getClass();
      $scripts[] = $this->path($class::PAGEBUILDER_SCRIPT);
    }
    $scripts = array_unique($scripts);
    foreach ($scripts as $path) {
      $this->addScript($path);
    }
  }

  /**
   * List all the Roles
   */
  public function actionIndex()
  {
    $max = $this->_user->getMaximumDisplayForApplication($this->_application);
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
      $form->newButton('submit', 'Save');
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
      
      $display = new \Jazzee\Entity\Display('role');
      $display->setRole($role);
      $display->setApplication($this->_application);
      $display->setName($role->getName() . ' display');
      foreach($this->_user->getMaximumDisplayForApplication($this->_application)->listElements() as $userDisplayElement){
        $displayElement = \Jazzee\Entity\DisplayElement::createFromDisplayElement($userDisplayElement, $this->_application);
        $display->addElement($displayElement);
        $this->getEntityManager()->persist($displayElement);
      }
      $this->_em->persist($display);
      $this->_em->flush();
 
      $this->addMessage('success', "Role Saved Successfully");
      $this->redirectPath('setup/roles');
    }
  }

  /**
   * Set the maximum display for a role
   * @param integer $roleID
   */
  public function actionGetRoleDisplay($roleID)
  {
    $this->layout = 'json';
    if ($role = $this->_em->getRepository('\Jazzee\Entity\Role')->findOneBy(array('id' => $roleID, 'program' => $this->_program->getId()))) {
      if(!$display = $role->getDisplay()){
        $display = new \Jazzee\Entity\Display('role');
        $display->setRole($role);
        $display->setApplication($this->_application);
        $display->setName($role->getName() . ' display');
        $this->_em->persist($display);
        $this->_em->flush();
      }
      $displayArray = array(
        'type' => 'role',
        'id'  => $display->getId(),
        'name' => $display->getName(),
        'pageIds' => $display->getPageIds(),
        'elementIds' => $display->getElementIds(),
        'elements' => $display->listElements(),
        'roleId'  => $display->getRole()->getId()
      );
      $this->setVar('result', $displayArray);
    } else {
      $this->addMessage('error', "Error: Role #{$roleID} does not exist.");
    }
    $this->loadView('setup_roles/result');
  }

  /**
   * Save the display
   */
  public function actionSaveDisplay()
  {
    $this->layout = 'json';
    $obj = json_decode($this->post['display']);
    if ($role = $this->_em->getRepository('\Jazzee\Entity\Role')->findOneBy(array('id' => $obj->roleId, 'program' => $this->_program->getId())) and $display = $role->getDisplay()) {
      $display->setName($obj->name);
      foreach ($display->getElements() as $displayElement) {
        $display->getElements()->removeElement($displayElement);
        $this->getEntityManager()->remove($displayElement);
      }
      $maximumUserDisplay = $this->_user->getMaximumDisplayForApplication($this->_application);
      foreach($obj->elements as $eObj){
        $tempDisplayElement = new \Jazzee\Display\Element($eObj->type, $eObj->title, $eObj->weight, $eObj->name, isset($eObj->pageId)?$eObj->pageId:null);
        if($maximumUserDisplay->hasDisplayElement($tempDisplayElement)){
          $displayElement = \Jazzee\Entity\DisplayElement::createFromDisplayElement($tempDisplayElement, $this->_application);
          $display->addElement($displayElement);
          $this->getEntityManager()->persist($displayElement);
        }
      }
      $this->_em->persist($display);
      $this->addMessage('success', $display->getName() . ' saved');
    }
    $this->setVar('result', 'nothing');
    $this->loadView('setup_roles/result');
  }

  /**
   * Delete the role display
   */
  public function actionDeleteDisplay()
  {
    $this->layout = 'json';
    $obj = json_decode($this->post['display']);
    if ($role = $this->_em->getRepository('\Jazzee\Entity\Role')->findOneBy(array('id' => $obj->roleId, 'program' => $this->_program->getId())) and $display = $role->getDisplay()) {
      $this->addMessage('success', $display->getName() . ' deleted');
      $this->getEntityManager()->remove($display);
    }
    $this->setVar('result', 'nothing');
    $this->loadView('setup_roles/result');
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

  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null)
  {
    //several views are controller by the complete action
    if (in_array($action, array('saveDisplay', 'deleteDisplay'))) {
      $action = 'getRoleDisplay';
    }
    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}