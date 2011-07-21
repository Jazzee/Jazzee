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
    $allThreads = $this->_em->getRepository('\Jazzee\Entity\Thread')->findByApplication($this->_application);
    $threads = array();
    foreach($allThreads as $thread) if($thread->hasUnreadMessage(\Jazzee\Entity\Message::APPLICANT)) $threads[] = $thread;
    $this->setVar('threads', $threads);
  }
  
  /**
   * List all messages
   */
  public function actionAll(){
    $threads = $this->_em->getRepository('\Jazzee\Entity\Thread')->findByApplication($this->_application);
    $this->setVar('threads', $threads);
  }
  
  /**
   * View a single message
   * 
   * @param integer $threadId
   */
  public function actionSingle($threadId) {
    $thread = $this->_em->getRepository('\Jazzee\Entity\Thread')->find($threadId);
    if(!$thread) throw new \Jazzee\Exception("{$threadId} is not a valid thread id");
    $applicant = $this->getApplicantById($thread->getApplicant()->getId());
    $this->setVar('thread', $thread);
  }
  
  /**
   * Mark message as unread
   * 
   * @param integer $messageId
   */
  public function actionMarkUnread($messageId) {
    $message = $this->_em->getRepository('\Jazzee\Entity\Message')->find($messageId);
    if(!$message) throw new \Jazzee\Exception("{$messageId} is not a valid message id");
    $applicant = $this->getApplicantById($message->getThread()->getApplicant()->getId());
    $message->unRead();
    $this->_em->persist($message);
    $this->addMessage('success', 'Message marked as unread');
    $this->redirectPath('applicants/messages');
  }
  
  /**
   * Reply to a message
   * 
   * @param integer $threadId
   */
  public function actionReply($threadId) {
    $thread = $this->_em->getRepository('\Jazzee\Entity\Thread')->find($threadId);
    if(!$thread) throw new \Jazzee\Exception("{$threadId} is not a valid thread id");
    $applicant = $this->getApplicantById($thread->getApplicant()->getId());
    $this->setVar('thread', $thread);
    
    $form = new \Foundation\Form();
    $form->setAction($this->path('applicants/messages/reply/' . $thread->getId()));
    
    $field = $form->newField();
    $field->setLegend('Reply');
    
    $element = $field->newElement('Textarea', 'text');
    $element->setLabel('Your Reply');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $form->newButton('submit', 'Submit');
    $this->setVar('form', $form);
    
    if($input = $form->processInput($this->post)){
      $reply = new \Jazzee\Entity\Message();
      $reply->setSender(\Jazzee\Entity\Message::PROGRAM);
      $reply->setText($input->get('text'));
      $thread->addMessage($reply);
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
    $threads = $cron->getEntityManager()->getRepository('\Jazzee\Entity\Thread')->findAll();
    $applications = array();
    $applicants = array();
    foreach($threads as $thread){
      if($thread->hasUnreadMessage(\Jazzee\Entity\Message::APPLICANT)){
        if(!array_key_exists($thread->getApplicant()->getApplication()->getId(), $applications)){
          $applications[$thread->getApplicant()->getApplication()->getId()] = array(
            'application' => $thread->getApplicant()->getApplication(),
            'count' => 0
          );
        }
        $applications[$thread->getApplicant()->getApplication()->getId()]['count']++;
      }
      //only send applicant reminders every 7 days
      if($thread->hasUnreadMessage(\Jazzee\Entity\Message::PROGRAM) and ($thread->getLastUnreadMessage(\Jazzee\Entity\Message::PROGRAM)->getCreatedAt()->diff(new \DateTime('now'))->days%7 == 0)){
        if(!array_key_exists($thread->getApplicant()->getId(), $applicants)){
          $applicants[$thread->getApplicant()->getId()] = array(
            'applicant' => $thread->getApplicant(),
            'count' => 0
          );
        }
        $applicants[$thread->getApplicant()->getId()]['count']++;
      }
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
    foreach($applicants as $arr){
      $message = $cron->newMessage();
      $message->AddAddress(
        $arr['applicant']->getEmail(),
        $arr['applicant']->getFullName());
      $message->Subject = 'New Message from ' . $arr['applicant']->getApplication()->getCycle()->getName() . ' ' . $arr['applicant']->getApplication()->getProgram()->getName();
      $body = 'You have ' . $arr['count'] . ' unread message(s) from the ' . $arr['applicant']->getApplication()->getCycle()->getName() . ' ' . $arr['applicant']->getApplication()->getProgram()->getName() . '.' .
        "\nYou can review them by logging into the application at: " . $cron->applyPath('apply/' . $arr['applicant']->getApplication()->getProgram()->getShortName() . '/' . $arr['applicant']->getApplication()->getCycle()->getName() . '/applicant/login');
      $message->Body = $body;
      $message->Send();
    }
  }
  
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null){
    //all action authorizations are controlled by the index action
    return parent::isAllowed($controller, 'index', $user, $program, $application);
  }
}