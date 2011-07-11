<?php
/**
 * The status portal that is displayed to applicants once thier application is locked
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage apply
 */
 
class ApplyStatusController extends \Jazzee\ApplyController {  
  /**
   * Status array
   * @var array
   */
  protected $status;
  
  public function beforeAction(){
    parent::beforeAction();
    //if the applicant hasn't locked and the application isn't closed
    if(!$this->_applicant->isLocked() AND $this->_application->getClose() > new DateTime('now')){
      $this->addMessage('notice', "You have not completed your application.");
      reset($this->_pages);
      $first = key($this->_pages);
    
      $this->redirectPath('apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/page/' . $first);
    }
    $this->setVar('applicant', $this->_applicant);
  }
  
  /**
   * Display the page
   */
  public function actionIndex() {
    $pages = array();
    foreach($this->_pages as $key => $page)if($page->answerStatusDisplay()) $pages[] = $page;
    $this->setVar('pages', $pages);
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
   * Admit Letter
   */
  public function actionAdmitLetter(){
    $text = $this->_application->getAdmitLetter();
    $search = array(
     '%Admit_Date%',
     '%Applicant_Name%',
     '%Offer_Response_Deadline%'
    );
    $replace = array();
    $replace[] = $this->_applicant->getDecision()->getFinalAdmit()->format('F jS Y');
    $replace[] = $this->_applicant->getFullName();
    $replace[] = $this->_applicant->getDecision()->getOfferResponseDeadline()->format('F jS Y g:ia');
    $text = str_ireplace($search, $replace, $text);
    
    $text = nl2br($text);
    $this->setVar('text', $text);
  }
  
  /**
   * Deny Letter
   */
  public function actionDenyLetter(){
    $text = $this->_application->getDenyLetter();
    $search = array(
     '%Deny_Date%',
     '%Applicant_Name%'
    );
    $replace = array();
    $replace[] = $this->_applicant->getDecision()->getFinalDeny()->format('F jS Y');
    $replace[] = $this->_applicant->getFullName();
    $text = str_ireplace($search, $replace, $text);
    
    $text = nl2br($text);
    $this->setVar('text', $text);
  }
  
  /**
   * Navigation
   * @return Navigation
   */
  public function getNavigation(){
    $navigation = new \Foundation\Navigation\Container();
    $menu = new \Foundation\Navigation\Menu();
    
    $menu->setTitle('Navigation');

    $path = 'apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/status';
    $link = new \Foundation\Navigation\Link('Your Status');
    $link->setHref($this->path($path));
    $menu->addLink($link); 
    if($this->_applicant->getDecision() and $this->_applicant->getDecision()->getFinalAdmit() and !($this->_applicant->getDecision()->getAcceptOffer() or $this->_applicant->getDecision()->getDeclineOffer()) ){
      $link = new \Foundation\Navigation\Link('Confirm Enrolment');
      $link->setHref($this->path($path . '/sir'));
      $menu->addLink($link); 
    }
    if($this->_applicant->getDecision() and $this->_applicant->getDecision()->getFinalAdmit()){
      $link = new \Foundation\Navigation\Link('View Decision Letter');
      $link->setHref($this->path($path . '/admitLetter'));
      $menu->addLink($link); 
    }
    if($this->_applicant->getDecision() and $this->_applicant->getDecision()->getFinalDeny()){
      $link = new \Foundation\Navigation\Link('View Decision Letter');
      $link->setHref($this->path($path . '/denyLetter'));
      $menu->addLink($link); 
    }
    $link = new \Foundation\Navigation\Link('Logout');
    $link->setHref($this->path('apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/applicant/logout'));
    $menu->addLink($link);
    
    $navigation->addMenu($menu);
    return $navigation;
  }
  
}
?>
