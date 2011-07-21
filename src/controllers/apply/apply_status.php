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
    if(!$this->_applicant->isLocked()){
      $statusPageText = $this->_application->getStatusIncompleteText();
    } else {
      switch($this->_applicant->getDecision()->status()){
        case 'finalDeny': $statusPageText = $this->_application->getStatusDenyText(); break;
        case 'finalAdmit': $statusPageText = $this->_application->getStatusAdmitText(); break;
        case 'acceptOffer': $statusPageText = $this->_application->getStatusAcceptText(); break;
        case 'declineOffer': $statusPageText = $this->_application->getStatusDeclineText(); break;
        default: $statusPageText = $this->_application->getStatusNoDecisionText();
      }
    }
    $search = array(
     '%Applicant_Name%',
     '%Application_Deadline%',
     '%Offer_Response_Deadline%',
     '%SIR_Link%',
     '%Admit_Letter%',
     '%Deny_Letter%',
     '%Admit_Date%',
     '%Deny_Date%',
     '%Accept_Date%',
     '%Decline_Date%'
    );
    $path = 'apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/status';
    $offerResponseDeadline = $this->_applicant->getDecision()?$this->_applicant->getDecision()->getOfferResponseDeadline()->format('l F jS Y g:ia'):null;
    $replace = array(
     $this->_applicant->getFullName(),
     $offerResponseDeadline,
     $this->_application->getClose()->format('l F jS Y g:ia'),
     $this->path($path . '/sir'),
     $this->path($path . '/admitLetter'),
     $this->path($path . '/denyLetter')
    );
    $replace[] = ($this->_applicant->getDecision()->getFinalAdmit())?$this->_applicant->getDecision()->getFinalAdmit()->format('l F jS Y g:ia'):null;
    $replace[] = ($this->_applicant->getDecision()->getFinalDeny())?$this->_applicant->getDecision()->getFinalDeny()->format('l F jS Y g:ia'):null;
    $replace[] = ($this->_applicant->getDecision()->getAcceptOffer())?$this->_applicant->getDecision()->getAcceptOffer()->format('l F jS Y g:ia'):null;
    $replace[] = ($this->_applicant->getDecision()->getDeclineOffer())?$this->_applicant->getDecision()->getDeclineOffer()->format('l F jS Y g:ia'):null;
    
    $statusPageText = str_ireplace($search, $replace, $statusPageText);
    $this->setVar('statusPageText', nl2br($statusPageText));
    $pages = array();
    foreach($this->_pages as $key => $page)if($page->answerStatusDisplay()) $pages[] = $page;
    $this->setVar('pages', $pages);
  }
  
  /**
   * SIR Form
   */
  public function actionSir(){
    if($this->_applicant->getDecision()->status() != 'finalAdmit') throw new \Jazzee\Exception('Applicant is not in the status finalAdmit');
    $form = new \Foundation\Form();
    $form->setAction($this->path('apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/status/sir'));
    $field = $form->newField();
    $field->setLegend('Confirm Enrolment');
    $field->setInstructions('You must confirm your enrollment by <strong><em>' . $this->_applicant->getDecision()->getOfferResponseDeadline()->format('l F jS Y g:ia') . '</em></strong>. If you do not confirm your enrollment your space may be released to another applicant.');
    $element = $field->newElement('RadioList', 'confirm');
    $element->setLabel('Do you intend to register for the term in which you applied?');
    $element->newItem(0,'No');
    $element->newItem(1,'Yes');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $form->newButton('submit', 'Save');
    $this->setVar('form', $form);
    if($input = $form->processInput($this->post)){
      if($input->get('confirm')){
        $this->_applicant->getDecision()->acceptOffer();
      } else {
        $this->_applicant->getDecision()->declineOffer();
      }
      $this->_em->persist($this->_applicant);
      $this->addMessage('success', 'Your intent was recorded.');
      $this->redirectPath('apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/status');
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
    if($this->_applicant->getDecision() and $this->_applicant->getDecision()->status() == 'finalAdmit'){
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
