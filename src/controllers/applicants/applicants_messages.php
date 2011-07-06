<?php
/**
 * Messages from applicants
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage applicants
 */
class ApplicantsMessagesController extends \Jazzee\AdminController {
  const MENU = 'Applicants';
  const TITLE = 'Messages';
  const PATH = 'applicants/messages';
  
  const ACTION_INDEX = 'View Messages';
  
  /**
   * List all unread messages
   */
  public function actionIndex(){
    $allMessages = $this->_em->getRepository('\Jazzee\Entity\Message')->findThreadByApplication($this->_application);
    $threads = array();
    foreach($allMessages as $message) if(!$message->isReadThread(\Jazzee\Entity\Message::PROGRAM)) $threads[] = $message;
    
    $this->setVar('threads', $threads);
  }
  
  /**
   * List all messages
   */
  public function actionAll(){
    $threads = $this->_em->getRepository('\Jazzee\Entity\Message')->findThreadByApplication($this->_application);
    $this->setVar('threads', $threads);
  }
  
  /**
   * View a single message
   * 
   * @param integer $messageId
   */
  public function actionSingle($messageId) {
    $message = $this->_em->getRepository('\Jazzee\Entity\Message')->find($messageId);
    if(!$message) throw new \Jazzee\Exception("{$messageId} is not a valid message id");
    $applicant = $this->getApplicantById($message->getApplicant()->getId());
    $this->setVar('message', $message);
  }
  
  /**
   * Mark message as unread
   * 
   * @param integer $messageId
   */
  public function actionMarkUnread($messageId) {
    $message = $this->_em->getRepository('\Jazzee\Entity\Message')->find($messageId);
    if(!$message) throw new \Jazzee\Exception("{$messageId} is not a valid message id");
    $applicant = $this->getApplicantById($message->getApplicant()->getId());
    $message->unRead();
    $this->addMessage('success', 'Message marked as unread');
    $this->redirectPath('applicants/messages');
  }
  
  /**
   * Reply to a message
   * 
   * @param integer $messageId
   */
  public function actionReply($messageId) {
    $form = new \Foundation\Form();
    $message = $this->_em->getRepository('\Jazzee\Entity\Message')->find($messageId);
    if(!$message) die;
    $applicant = $this->getApplicantById($message->getApplicant()->getId());
    $this->setVar('message', $message);
    $form->setAction($this->path('applicants/messages/reply/' . $message->getId()));
    
    $field = $form->newField();
    $field->setLegend('Reply to message');
    
    $element = $field->newElement('TextInput', 'subject');
    $element->setLabel('Subject');
    $element->setValue('re: ' . $message->getSubject());
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('Textarea', 'text');
    $element->setLabel('Your Reply');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $form->newButton('submit', 'Submit');
    $this->setVar('form', $form);
    
    if($input = $form->processInput($this->post)){
      $reply = new \Jazzee\Entity\Message();
      $reply->setApplicant($applicant);
      $reply->setSender(\Jazzee\Entity\Message::PROGRAM);
      $message->setReply($reply);
      $reply->setSubject($input->get('subject'));
      $reply->setText($input->get('text'));
      $this->_em->persist($reply);
      $this->addMessage('success', 'Your reply has been sent.');
      $this->redirectPath('applicants/messages');
    }
  }
  
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null){
    //all action authorizations are controlled by the index action
    return parent::isAllowed($controller, 'index', $user, $program, $application);
  }
}