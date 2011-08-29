<?php
/**
 * Change the a users current program and defautl program
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 */
class AdminChangeprogramController extends \Jazzee\AdminController {
  const MENU = 'My Account';
  const TITLE = 'Change Program';
  const PATH = 'changeprogram';
  const REQUIRE_AUTHORIZATION = true;
  const REQUIRE_APPLICATION = false;
  
//  const ACTION_INDEX = 'Change to Authorized Program'; dont display this as a role option any user can do it
  const ACTION_ANYPROGRAM = 'Change to any Program';
  
  /**
   * Display index
   */
  public function actionIndex(){
    $form = new \Foundation\Form();
    $form->setAction($this->path('changeprogram'));
    $field = $form->newField();
    $field->setLegend('Select Program');
    $element = $field->newElement('SelectList','program');
    $element->setLabel('Program');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $programs = $this->_em->getRepository('\Jazzee\Entity\Program')->findBy(array('isExpired' => false), array('name' => 'ASC'));
    $userPrograms = $this->_user->getPrograms();
    foreach($programs as $program){
      if($this->checkIsAllowed($this->controllerName, 'anyProgram') or in_array($program->getId(), $userPrograms)) $element->newItem($program->getId(), $program->getName());
    }
    if($this->_program) $element->setValue($this->_program->getId());
    //only ask if the user already has a default cycle
    if($this->_user->getDefaultProgram()){
      $element = $field->newElement('RadioList','default');
      $element->setLabel('Set as your default');
      $element->newItem(0, 'No');
      $element->newItem(1, 'Yes');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    }
    $form->newButton('submit', 'Change Program');
    
    if($input = $form->processInput($this->post)){
      $this->_program = $this->_em->getRepository('\Jazzee\Entity\Program')->find($input->get('program'));

      //if they wish it, or if the user has no default cycle
      if(!$this->_user->getDefaultProgram() OR $input->get('default')){
        $this->_user->setDefaultProgram($this->_program);
        $this->_em->persist($this->_user);
        $this->addMessage('success', 'Default program changed to ' . $this->_program->getName());
      }
      unset($this->_store->AdminControllerGetNavigation);
      $this->addMessage('success', 'Program changed to ' . $this->_program->getName());
      $this->redirectPath('welcome');
    }
    
    $this->setVar('form', $form);
  }
  
  /**
   * Change to any program
   * This method doesn't actually do anything it is just here to trigger an authorization lookup
   */
  public function actionAnyProgram(){
    throw new \Jazzee\Exception('adminChangeProgram::actionAnyProgram was called.  It should not have been.');
  }
  
  /**
   * Only allow change program if the user is in at least one program
   * At this top level always return false so nothing is allowed by default
   * @param string $controller
   * @param string $action
   * @param \Jazzee\Entity\User $user
   * @param \Jazzee\Entity\Program $program
   * @return bool
   */
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null){
    if($user and $action=='index'){
      $userPrograms = $user->getPrograms();
      return (parent::isAllowed($controller, 'anyprogram', $user) or !empty($userPrograms));
    }
    return parent::isAllowed($controller, $action, $user, $program, $application);
  }
}
?>