<?php
/**
 * Complete a recommendation
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class LorController extends JazzeeController{
  
  /**
   * The index page
   * If the recommendation hasn't been completed show the form
   * If it has been completed show a confirmation
   * If no match is found or the key then print a 404 error
   * @param string $urlKey 
   */
  public function actionIndex($urlKey){
    $q = Doctrine_Query::create()
    ->select('r.*, rp.*')
    ->from('Recommendation r')
    ->leftJoin('r.RecommendationPage rp')
    ->leftJoin('r.LORAnswer an')
    ->where('r.urlKey = ?', array($urlKey));
    $recommendation = $q->fetchOne();
    if(!$recommendation) $this->send404();
    $page = $recommendation->RecommendationPage->LORPage;
    if($recommendation->LORAnswer->exists()){
      $this->loadView($this->controllerName . '/complete');
      exit;
    }
    $this->setVar('page', $page);
    $form = new Form;
    $form->action = $this->path("lor/{$urlKey}");
    $field = $form->newField();
    $field->legend = $page->title;
    $field->instructions = $page->instructions;
    foreach($page->Elements as $e){
      $element = new $e->ElementType->class($e);
      $element->addToField($field);
    }
    $form->newButton('submit', 'Save');
    $form->newButton('reset', 'Clear Form');
    if($input = $form->processInput($this->post)){
      $a = new Answer;
      $a->pageID = $page->id;
      $a->applicantID = $recommendation->Answer->Applicant->id;
      foreach($page->Elements as $e){
        $element = new $e->ElementType->class($e);
        $element->setValueFromInput($input->{'el'.$e->id});
        foreach($element->getAnswers() as $elementAnswer){
          $a->Elements[] = $elementAnswer;
        }
      }
      $a->save();
      $recommendation->LORAnswerID = $a->id;
      $recommendation->save();
      $this->messages->write('success', 'Recommendation Saved Successfully');
      
      $this->setVar('answer', $a);
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
    $request->setActionParams(array('error' => '404', 'message'=>'File Not Found'));
  
    // Get a new front controller without any routers, and have it process our handmade request.
    $fc = new Lvc_FrontController();
    $fc->processRequest($request);
    exit();
  }
}
?>