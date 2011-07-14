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
  
  const MIN_INTERVAL = 28800; //8 hours
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
  
  /**
   * Check for new messages in each program and email the administrator
   * 
   * @param AdminCronController $cron
   */
  public static function runCron(AdminCronController $cron){
    if(time() - (int)$cron->getVar('applicantsMessagesLastRun') < self::MIN_INTERVAL) return false;
    $cron->setVar('applicantsMessagesLastRun', time());
    $threads = $cron->getEntityManager()->getRepository('\Jazzee\Entity\Message')->findAll(array('parent' => null));
    $applications = array();
    foreach($threads as $thread) if(!$thread->isReadThread(\Jazzee\Entity\Message::PROGRAM)){
      if(!array_key_exists($thread->getApplicant()->getApplication()->getId(), $applications)){
        $applications[$thread->getApplicant()->getApplication()->getId()] = array(
          'application' => $thread->getApplicant()->getApplication(),
          'count' => 0
        );
      }
      $applications[$thread->getApplicant()->getApplication()->getId()]['count']++;
    }
    foreach($applications as $arr){
      $message = $cron->newMessage();
      $message->AddAddress(
        $arr['application']->getContactEmail(),
        $arr['application']->getContactName());
      $message->Subject = 'New Applicant Messages for ' . $arr['application']->getCycle()->getName() . ' ' . $arr['application']->getProgram()->getName();
      $body = 'There are ' . $arr['count'] . ' unread messages for the ' . $arr['application']->getCycle()->getName() . ' ' . $arr['application']->getProgram()->getName() . ' program.' .
        "\nYou can review them at: " . $cron->path('applicants/messages');
      $message->Body = $body;
      $message->Send();
    }
  }
  
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null){
    //all action authorizations are controlled by the index action
    return parent::isAllowed($controller, 'index', $user, $program, $application);
  }
}