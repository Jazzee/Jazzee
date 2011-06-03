<?php
/**
 * Manage Global Roles
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
class ManageRolesController extends \Jazzee\AdminController {
  const MENU = 'Manage';
  const TITLE = 'Roles';
  const PATH = 'manage/roles';
  
  const ACTION_INDEX = 'View Roles';
  const ACTION_EDIT = 'Edit Role';
  const ACTION_NEW = 'New Role';
  
  /**
   * List all the Roles
   */
  public function actionIndex(){
    $this->setVar('roles', $this->_em->getRepository('\Jazzee\Entity\Role')->findByIsGlobal(true));
  }
  
  /**
   * Edit a role
   * @param integer $roleID
   */
   public function actionEdit($roleID){ 
    if($role = $this->_em->getRepository('\Jazzee\Entity\Role')->findOneBy(array('id' => $roleID, 'isGlobal'=>true))){
      $form = new \Foundation\Form;
      $form->setAction($this->path('admin/manage/roles/edit/' . $role->getId()));
      $field = $form->newField();
      $field->setLegend('Edit ' . $role->getName() . ' role');
      $element = $field->newElement('TextInput','name');
      $element->setLabel('Role Name');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->setValue($role->getName());
      $menus = $this->getControllerActions();
      ksort($menus);
      foreach($menus as $menu => $list){
        foreach($list as $controller){
          $element = $field->newElement('CheckboxList',$controller['name']);
          $element->setLabel($menu . ' ' . $controller['title'] . ' actions');
          foreach($controller['actions'] as $actionName => $actionTitle){
            $element->newItem($actionName, $actionTitle);
          }
          $values = array();
          foreach($role->getActions() as $action){
            if($action->getController() == $controller['name'])
              $values[] = $action->getAction();
          }
          $element->setValue($values);
        }
      }
      $form->newButton('submit', 'Edit Role');
      $this->setVar('form', $form);
      if($input = $form->processInput($this->post)){
        $role->setName($input->get('name'));
        foreach($role->getActions() as $action){
          $this->_em->remove($action);
          $role->getActions()->removeElement($action);
        }
        
        foreach($menus as $menu => $list){
          foreach($list as $controller){
            $actions = $input->get($controller['name']);
            if(!empty($actions)){
              foreach($actions as $actionName){
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
        $this->redirectPath('admin/manage/roles/');
      }
    } else {
      $this->addMessage('error', "Error: Role #{$roleID} does not exist.");
    }
  }
   
  /**
   * Create a new role
   */
   public function actionNew(){
    $form = new \Foundation\Form();
    $form->setAction($this->path("admin/manage/roles/new"));
    $field = $form->newField();
    $field->setLegend('New Global Role');
    $element = $field->newElement('TextInput','name');
    $element->setLabel('Role Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $form->newButton('submit', 'Add Role');
    $this->setVar('form', $form); 
    if($input = $form->processInput($this->post)){
      $role = new \Jazzee\Entity\Role();
      $role->makeGlobal();
      $role->setName($input->get('name'));
      $this->_em->persist($role);
      $this->addMessage('success', "Role Saved Successfully");
      $this->redirectPath("manage/roles/");
    }
  }
  
  /**
   * Get All of the possible controllers and actions
   * @return array of ControllerAuths
   */
  protected function getControllerActions(){
    $controllers = array();
    foreach($this->listControllers() as $controller){
      $class = \Foundation\VC\Config::getControllerClassName($controller);
      $arr = array('name'=> $controller, 'title' => $class::TITLE, 'actions'=>array());
      foreach(get_class_methods($class) as $method){
        if(substr($method, 0, 6) == 'action'){
          $constant = 'ACTION_' . strtoupper(substr($method, 6));
          if(defined("{$class}::{$constant}")) $arr['actions'][strtolower(substr($method, 6))] = constant("{$class}::{$constant}");
        }
      }
      if(!empty($arr['actions'])) $controllers[$class::MENU][] = $arr;
    }
    return $controllers;
  }
}
?>