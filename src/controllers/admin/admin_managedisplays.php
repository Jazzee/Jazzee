<?php

/**
 * Allows a user to manage their displays
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class AdminManagedisplaysController extends \Jazzee\AdminController
{
  const PATH = 'managedisplays';
  /**
   * Add the required JS
   */
  public function setUp()
  {
    parent::setUp();
    $this->setLayoutVar('status', 'success');
    $this->layout = 'json';
    $this->setVar('result', 'nothing');
  }

  /**
   * Create a new display
   */
  public function actionNew()
  {
    $display = new \Jazzee\Entity\Display;
    $display->setName('New');
    $display->setUser($this->_user);
    $display->setApplication($this->_application);
    $this->_em->persist($display);
    $this->_em->flush();
    $this->addMessage('success', 'Created new display');
    $this->setVar('result', $display->getId());
    $this->loadView('applicants_single/result');
  }

  /**
   * Create a new display
   */
  public function actionDelete()
  {
    $obj = json_decode($this->post['display']);
    if($display = $this->_em->getRepository('Jazzee\Entity\Display')->findOneBy(array('id'=>$obj->id, 'user'=>$this->_user))){
      $this->addMessage('success', $display->getName() . ' deleted');
      $this->getEntityManager()->remove($display);
    }
    $this->loadView('applicants_single/result');
  }

  /**
   * Create a new display
   */
  public function actionSave()
  {
    $obj = json_decode($this->post['display']);
    if($display = $this->_em->getRepository('Jazzee\Entity\Display')->findOneBy(array('id'=>$obj->id, 'user'=>$this->_user))){
      $display->setName($obj->name);
      foreach ($display->getElements() as $displayElement) {
        $display->getElements()->removeElement($displayElement);
        $this->getEntityManager()->remove($displayElement);
      }
      foreach($obj->elements as $eObj){
        switch($eObj->type){
          case 'applicant':
            $displayElement = new \Jazzee\Entity\DisplayElement('applicant');
            $displayElement->setName($eObj->name);
            break;
          case 'page':
            $displayElement = new \Jazzee\Entity\DisplayElement('page');
            $element = $this->_application->getElementById($eObj->name);
            $displayElement->setElement($element);
            break;
          default:
            throw new \Jazzee\Exception("{$eObj->type} is not a valid DisplayElement type");
        }
        $displayElement->setTitle($eObj->title);
        $displayElement->setWeight($eObj->weight);
        $display->addElement($displayElement);
        $this->getEntityManager()->persist($displayElement);
      }
      $this->_em->persist($display);
      $this->addMessage('success', $display->getName() . ' saved');
    }
    $this->loadView('applicants_single/result');
  }

  /**
   * Any user can access
   * @param string $controller
   * @param string $action
   * @param \Jazzee\Entity\User $user
   * @param \Jazzee\Entity\Program $program
   * @return bool
   */
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null)
  {
    if (in_array($action, array('save', 'new', 'delete')) AND $user) {
      return true;
    }

    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}