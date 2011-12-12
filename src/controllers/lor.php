<?php
/**
 * Complete a recommendation
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class LorController extends \Jazzee\Controller{  
  /**
   * The index page
   * If the recommendation hasn't been completed show the form
   * If it has been completed show a confirmation
   * If no match is found or the key then print a 404 error
   * @param string $urlKey 
   */
  public function actionIndex($urlKey){
    $answer = $this->_em->getRepository('\Jazzee\Entity\Answer')->findOneBy(array('uniqueId'=>$urlKey));
    if(!$answer OR !$answer->isLocked()) $this->send404();
    if($answer->getChildren()->count()){
      $this->loadView($this->controllerName . '/complete');
      exit;
    }
    $this->setVar('answer', $answer);
    
    $page = $answer->getPage()->getChildren()->first();
    $this->setVar('page', $page);
    
    
    if(!$deadline = $page->getParent()->getVar('lorDeadline')){
      $deadline = $answer->getApplicant()->getApplication()->getClose()->format('c');
    }
    $deadline = new \DateTime($deadline);
    $this->setVar('deadline', $deadline->format('m/d/Y g:ia T'));
    if($page->getParent()->getVar('lorDeadlineEnforced') and $deadline < new \DateTime('now')){
      $this->loadView($this->controllerName . '/missed_deadline');
      exit;
    }
    

    $form = new \Foundation\Form;
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('lor/'.$urlKey));
    $field = $form->newField();
    $field->setLegend($page->getTitle());
    $field->setInstructions($page->getInstructions());
    foreach($page->getElements() as $element){
      $element->getJazzeeElement()->setController($this);
      $element->getJazzeeElement()->addToField($field);
    }
    $form->newButton('submit', 'Submit Recommendation');
    $form->newButton('reset', 'Clear Form');
    
    if($input = $form->processInput($this->post)){
      $childAnswer = new \Jazzee\Entity\Answer();
      $childAnswer->setParent($answer);
      $childAnswer->setApplicant($answer->getApplicant());
      $childAnswer->setPage($page);
      foreach($page->getElements() as $element){
        $element->getJazzeeElement()->setController($this);
        foreach($element->getJazzeeElement()->getElementAnswers($input->get('el'.$element->getId())) as $elementAnswer){
          $childAnswer->addElementAnswer($elementAnswer);
        }
      }
      $this->_em->persist($childAnswer);
      $this->addMessage('success', 'Recommendation Received');
      //flush here so the answerId will be correct when we view
      $this->_em->flush();
      
      $this->setVar('answer', $childAnswer);
      $this->loadView($this->controllerName . '/review');
      exit();
    }
    $this->setVar('form', $form);
    $this->setVar('applicantName', $answer->getApplicant()->getFullName());
    $this->setLayoutVar('layoutTitle', $answer->getApplicant()->getApplication()->getCycle()->getName() . ' ' . $answer->getApplicant()->getApplication()->getProgram()->getName() . ' Recommendation');
    
  }
  
  /**
   * Send a 404 error page
   */
  protected function send404(){
    $request = new Lvc_Request();
    $request->setControllerName('error');
    $request->setActionName('index');
    $request->setActionParams(array('error' => '404', 'message'=>'We were unable to locate this recommendation, or it has already been submitted.'));
  
    // Get a new front controller without any routers, and have it process our handmade request.
    $fc = new Lvc_FrontController();
    $fc->processRequest($request);
    exit();
  }
}
?>