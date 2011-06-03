<?php
/**
 * Complete a recommendation
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class LorController extends \Jazzee\JazzeeController{
  
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
    
    $page = $answer->getPage()->getChildren()->first();
    $this->setVar('page', $page);
    
    $form = new \Foundation\Form;
    $form->setAction($this->path('lor/'.$urlKey));
    $field = $form->newField();
    $field->setLegend($page->getTitle());
    $field->setInstructions($page->getInstructions());
    foreach($page->getElements() as $element){
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