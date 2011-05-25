<?php
/**
 * The status portal that is displayed to applicants once thier application is locked
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage apply
 */
 
class ApplyStatusController extends ApplyController {  
  /**
   * Status array
   * @var array
   */
  protected $status;
  
  public function beforeAction(){
    parent::beforeAction();
    //if the applicant hasn't locked and the application isn't closed
    if(!$this->applicant->locked and strtotime($this->applicant->Application->close) > time()){
      $this->messages->write('notice', "You have not completed your application.");
      $this->redirect($this->path("apply/{$this->application['Program']->shortName}/{$this->application['Cycle']->name}/page/{$this->application['Pages']->getFirst()->id}"));
      $this->afterAction();
      die();
    }
    $this->status = array('deny'=>false,'admit'=>false,'accept'=>false,'decline'=>false);
    if($this->applicant->relatedExists('Decision')){
      if($this->applicant->Decision->finalDeny)
        $this->status['deny'] = true;
      if($this->applicant->Decision->finalAdmit)
        $this->status['admit'] = true;
      if($this->applicant->Decision->declineOffer)
        $this->status['decline'] = true;
      if($this->applicant->Decision->acceptOffer)
        $this->status['accept'] = true;
    }
    $this->setVar('status', $this->status);
    $this->setVar('applicant', $this->applicant);
  }
  
  /**
   * Display the page
   */
  public function actionIndex() {
    $pages = array();
    foreach($this->application->Pages as $page){
      if($page->Page->showAnswerStatus == true){
        $pages[] = $this->pages[$page->id];
      }
    }
    $this->setVar('answerStatusPages', $pages);
  }
  
  /**
   * SIR Form
   */
  public function actionSir(){
    $form = new Form;
    $form->action = $this->path("apply/{$this->application['Program']->shortName}/{$this->application['Cycle']->name}/status/sir");
    $field = $form->newField();
    $field->legend = 'Confirm Enrolment';
    $field->instructions = 'You must confirm your enrollment by <strong><em>' . $this->applicant->Decision->offerResponseDeadline . '</em></strong>. If you do not confirm your enrollment your space may be released to another applicant.';
    $element = $field->newElement('RadioList', 'confirm');
    $element->label = 'Do you intend to register for the quarter in which you applied?';
    $element->addItem(0,'No');
    $element->addItem(1,'Yes');
    $element->addValidator('NotEmpty');
    $form->newButton('submit', 'Save');
    $this->setVar('form', $form);
    if($input = $form->processInput($this->post)){
      if($input->confirm){
        $this->applicant->Decision->acceptOffer();
      } else {
        $this->applicant->Decision->declineOffer();
      }
      $this->applicant->save();
      $this->redirect($this->path("apply/{$this->application['Program']->shortName}/{$this->application['Cycle']->name}/status"));
    }
  }
  
  /**
   * Navigation
   * @return Navigation
   */
  public function getNavigation(){
    $navigation = new Navigation;
    $menu = $navigation->newMenu();
    $menu->title = 'Navigation';
    $menu->newLink(array('text'=>'Your Status', 'href'=>$this->path("apply/{$this->application['Program']->shortName}/{$this->application['Cycle']->name}/status")));
    if($this->status['admit'] and (!$this->status['accept'] and !$this->status['decline']))
      $menu->newLink(array('text'=>'Confirm Enrolment', 'href'=>$this->path("apply/{$this->application['Program']->shortName}/{$this->application['Cycle']->name}/status/sir")));
    $menu->newLink(array('text'=>'Get Help', 'href'=>$this->path("apply/{$this->application['Program']->shortName}/{$this->application['Cycle']->name}/support/")));
    $menu->newLink(array('text'=>'Logout', 'href'=>$this->path("apply/{$this->application['Program']->shortName}/{$this->application['Cycle']->name}/applicant/logout")));
    return $navigation;
  }
  
}
?>
