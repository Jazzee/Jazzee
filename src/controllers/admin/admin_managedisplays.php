<?php

/**
 * Allows a user to manage their displays
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class AdminManagedisplaysController extends \Jazzee\AdminController
{
  //this goes in the setup menu even though it is User specific so it can be found
  const MENU = 'Setup';
  const TITLE = 'Manage Displays';
  const PATH = 'managedisplays';
  
  const ACTION_INDEX = 'Own Displays';

  /**
   * Add the required JS
   */
  public function setUp()
  {
    parent::setUp();
    $this->setLayoutVar('status', 'success');
    $this->addScript($this->path('resource/foundation/scripts/form.js'));
    $this->addScript($this->path('resource/scripts/controllers/admin_managedisplays.controller.js'));
  }

  /**
   * Display index
   */
  public function actionIndex()
  {
    $displays = $this->_em->getRepository('Jazzee\Entity\Display')->findByUserApplicationArray($this->_user, $this->_application);
    $this->setVar('displays', $displays);
  }

  /**
   * Edit a display
   */
  public function actionEdit($displayId)
  {
    if($display = $this->_em->getRepository('Jazzee\Entity\Display')->findOneBy(array('id'=>$displayId, 'user'=>$this->_user))){
      $pages = array();
      foreach($this->_application->getApplicationPages() as $applicationPage){
        if(is_subclass_of($applicationPage->getPage()->getType()->getClass(), 'Jazzee\Interfaces\DataPage')){
          $pages[] = $applicationPage;
        }
      }
      $this->setVar('applicationPages', $pages);

      if($this->post){
        $display->setName($this->post['displayName']);
        foreach ($display->getPages() as $app) {
          $display->getPages()->removeElement($app);
          $this->getEntityManager()->remove($app);
        }
        foreach($this->post['pages'] as $pageId){
          $applicationPage = $this->_application->getApplicationPageByPageId($pageId);
          $displayPage = new \Jazzee\Entity\DisplayPage;
          $display->addPage($displayPage);
          $displayPage->setApplicationPage($applicationPage);
          if(array_key_exists("page{$pageId}elements", $this->post)){
            foreach($this->post["page{$pageId}elements"] as $elementId){
              $element = $applicationPage->getPage()->getElementById($elementId);
              $displayElement = new \Jazzee\Entity\DisplayElement;
              $displayPage->addElement($displayElement);
              $displayElement->setElement($element);
              $this->getEntityManager()->persist($displayElement);
            }
          }
          $this->_em->persist($displayPage);
        }
        $this->_em->persist($display);
        $this->addMessage('success', $display->getName() . ' saved');
      }
      $this->setVar('display', $display);
    }
  }

  /**
   * Create a new display
   */
  public function actionNew()
  {
    $display = new \Jazzee\Entity\Display;
    $display->setName('New Display');
    $display->setUser($this->_user);
    $display->setApplication($this->_application);
    $this->_em->persist($display);
    $this->_em->flush();
    $this->addMessage('success', 'Created new display');
    $this->redirectPath('managedisplays/edit/'.$display->getId());
  }

  /**
   * Create a new display
   */
  public function actionDelete($displayId)
  {
    if($display = $this->_em->getRepository('Jazzee\Entity\Display')->findOneBy(array('id'=>$displayId, 'user'=>$this->_user))){
      $this->addMessage('success', $display->getName() . ' deleted');
      $this->getEntityManager()->remove($display);
      $this->redirectPath('managedisplays');
    }
  }

  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null)
  {
    //all action authorizations are controlled by the index action
    return parent::isAllowed($controller, 'index', $user, $program, $application);
  }

}