<?php

/**
 * Messages from applicants
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplicantsMessagesController extends \Jazzee\AdminController
{

  const MENU = 'Applicants';
  const TITLE = 'Messages';
  const PATH = 'applicants/messages';
  const ACTION_INDEX = 'View Messages';
  const MIN_INTERVAL_APPLICANTS = 3500; //1 hour minus a bit
  const MIN_INTERVAL_PROGRAMS = 86300; //24 hours minus a bit

  /**
   * List all unread messages
   */

  public function actionIndex()
  {
    $allThreads = $this->_em->getRepository('\Jazzee\Entity\Thread')->findByApplication($this->_application);
    $threads = array();
    foreach ($allThreads as $thread) {
      if ($thread->hasUnreadMessage(\Jazzee\Entity\Message::APPLICANT)) {
        $threads[] = $thread;
      }
    }
    $this->setVar('threads', $threads);
  }

  /**
   * List all messages
   */
  public function actionAll()
  {
    $threads = $this->_em->getRepository('\Jazzee\Entity\Thread')->findByApplication($this->_application);
    $this->setVar('threads', $threads);
  }

  /**
   * View a single message
   *
   * @param integer $threadId
   */
  public function actionSingle($threadId)
  {
    $thread = $this->_em->getRepository('\Jazzee\Entity\Thread')->find($threadId);
    if (!$thread) {
      throw new \Jazzee\Exception("{$threadId} is not a valid thread id");
    }
    //check to be sure the user has access to the thread
    $this->getApplicantById($thread->getApplicant()->getId());
    $this->setVar('thread', $thread);
  }

  /**
   * Mark message as unread
   *
   * @param integer $messageId
   */
  public function actionMarkUnread($messageId)
  {
    $message = $this->_em->getRepository('\Jazzee\Entity\Message')->find($messageId);
    if (!$message) {
      throw new \Jazzee\Exception("{$messageId} is not a valid message id");
    }
    //check to be sure the user has access to the thread
    $this->getApplicantById($message->getThread()->getApplicant()->getId());
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
  public function actionReply($threadId)
  {
    $thread = $this->_em->getRepository('\Jazzee\Entity\Thread')->find($threadId);
    if (!$thread) {
      throw new \Jazzee\Exception("{$threadId} is not a valid thread id");
    }
    $this->getApplicantById($thread->getApplicant()->getId()); //check to be sure the user has access to the thread
    $this->setVar('thread', $thread);

    $form = new \Foundation\Form();
    $form->setAction($this->path('applicants/messages/reply/' . $thread->getId()));

    $field = $form->newField();
    $field->setLegend('Reply');

    $element = $field->newElement('Textarea', 'text');
    $element->setLabel('Your Reply');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\SafeHTML($element));
    $form->newButton('submit', 'Submit');
    $this->setVar('form', $form);

    if ($input = $form->processInput($this->post)) {
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
   * New Message to applicant
   * @param $applicantId
   */
  public function actionNew($applicantId = false)
  {
    if ($applicantId) {
      $applicant = $this->getApplicantById($applicantId);
    }

    $form = new \Foundation\Form();
    $path = 'applicants/messages/new';
    if ($applicantId) {
      $path .= '/' . $applicantId;
    }
    $form->setAction($this->path($path));
    $field = $form->newField();
    $field->setLegend('New Message');

    if ($applicantId) {
      $element = $field->newElement('Plaintext', 'name');
      $element->setLabel('To');
      $element->setValue($applicant->getFullName());

      $element = $field->newElement('HiddenInput', 'to');
      $element->setValue($applicant->getId());
    } else {
      $element = $field->newElement('SelectList', 'to');
      $element->setLabel('To');
      foreach ($this->_em->getRepository('\Jazzee\Entity\Applicant')->findApplicantsByName('%', '%', $this->_application) as $applicant) {
        $element->newItem($applicant->getId(), $applicant->getLastName() . ', ' . $applicant->getFirstName());
      }
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    }
    $element = $field->newElement('TextInput', 'subject');
    $element->setLabel('Subject');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\SafeHTML($element));

    $element = $field->newElement('Textarea', 'text');
    $element->setLabel('Message');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\SafeHTML($element));
    $form->newButton('submit', 'Submit');
    $this->setVar('form', $form);

    if ($input = $form->processInput($this->post)) {
      $thread = new \Jazzee\Entity\Thread();
      $thread->setSubject($input->get('subject'));
      $applicant = $this->getApplicantById($input->get('to'));
      $thread->setApplicant($applicant);

      $message = new \Jazzee\Entity\Message();
      $message->setSender(\Jazzee\Entity\Message::PROGRAM);
      $message->setText($input->get('text'));
      $thread->addMessage($message);
      $this->_em->persist($thread);
      $this->_em->persist($message);
      $this->addMessage('success', 'Your message has been sent.');
      $this->redirectPath('applicants/messages');
    }
  }

  /**
   * Check for new messages in each program and email the administrator
   *
   * @param AdminCronController $cron
   */
  public static function runCron(AdminCronController $cron)
  {
    $threads = $cron->getEntityManager()->getRepository('\Jazzee\Entity\Thread')->findAll();
    if (time() - (int) $cron->getVar('applicantsMessagesApplicantsLastRun') > self::MIN_INTERVAL_APPLICANTS) {
      $lastRun = new DateTime();
      $lastRun->setTimeStamp((int) $cron->getVar('applicantsMessagesApplicantsLastRun'));
      $cron->setVar('applicantsMessagesApplicantsLastRun', time());
      $applicants = array();
      foreach ($threads as $thread) {
        if ($thread->hasUnreadMessage(\Jazzee\Entity\Message::PROGRAM)) {
          $createdAt = $thread->getLastUnreadMessage(\Jazzee\Entity\Message::PROGRAM)->getCreatedAt();
          $diff = $createdAt->diff(new DateTime('now'));
          //if created since our last run or it is a multiplier fo 7 days old in te hour it was crated
          //don't send messages to applicants who have logged in since the message was created
          if (($createdAt > $lastRun OR ($diff->days > 5 AND $diff->days % 7 == 0 AND $diff->h == 0)) AND $thread->getApplicant()->getLastLogin() < $createdAt) {
            if (!array_key_exists($thread->getApplicant()->getId(), $applicants)) {
              $applicants[$thread->getApplicant()->getId()] = array(
                'applicant' => $thread->getApplicant(),
                'count' => 0
              );
            }
            $applicants[$thread->getApplicant()->getId()]['count']++;
          }
        }
      }
      foreach ($applicants as $arr) {
        try {
          $message = $cron->newMailMessage();
          $message->AddAddress($arr['applicant']->getEmail(), $arr['applicant']->getFullName());
          $message->Subject = 'New Message from ' . $arr['applicant']->getApplication()->getCycle()->getName() . ' ' . $arr['applicant']->getApplication()->getProgram()->getName();
          $body = 'You have ' . $arr['count'] . ' unread message(s) from the ' . $arr['applicant']->getApplication()->getCycle()->getName() . ' ' . $arr['applicant']->getApplication()->getProgram()->getName() . ' program.' .
                  "\nYou can review your message(s) by logging into the application at: " . $cron->absoluteApplyPath('apply/' . $arr['applicant']->getApplication()->getProgram()->getShortName() . '/' . $arr['applicant']->getApplication()->getCycle()->getName() . '/applicant/login');
          $body .= "\nOnce you have logged into the application choose support in the upper right hand corner of the screen.";
          $message->Body = $body;
          $message->Send();
        } catch (phpmailerException $e) {
          $cron->log("Attempting to send message reminder to applicant #{$arr['applicant']->getId()} resulted in a mail exception: " . $e->getMessage());
        }
      }
      if ($count = count($applicants)) {
        $cron->log("Sent {$count} reminder messages to applicants.");
      }
    }
    if (time() - (int) $cron->getVar('applicantsMessagesProgramsLastRun') > self::MIN_INTERVAL_PROGRAMS) {
      $lastRun = new DateTime();
      $lastRun->setTimeStamp((int) $cron->getVar('applicantsMessagesProgramsLastRun'));
      $cron->setVar('applicantsMessagesProgramsLastRun', time());
      $applications = array();
      foreach ($threads as $thread) {
        if ($thread->hasUnreadMessage(\Jazzee\Entity\Message::APPLICANT)) {
          if (!array_key_exists($thread->getApplicant()->getApplication()->getId(), $applications)) {
            $applications[$thread->getApplicant()->getApplication()->getId()] = array(
              'application' => $thread->getApplicant()->getApplication(),
              'count' => 0
            );
          }
          $applications[$thread->getApplicant()->getApplication()->getId()]['count']++;
        }
      }
      foreach ($applications as $arr) {
        try {
          $message = $cron->newMailMessage();
          $message->AddAddress($arr['application']->getContactEmail(), $arr['application']->getContactName());
          $message->Subject = 'New Applicant Messages for ' . $arr['application']->getCycle()->getName() . ' ' . $arr['application']->getProgram()->getName();
          $body = 'There are ' . $arr['count'] . ' unread messages for the ' . $arr['application']->getCycle()->getName() . ' ' . $arr['application']->getProgram()->getName() . ' program.' .
                  "\nYou can review them at: " . $cron->absolutePath('applicants/messages');
          $message->Body = $body;
          $message->Send();
        } catch (phpmailerException $e) {
          $cron->log("Attempting to send message reminder to {$arr['application']->getCycle()->getName()} {$arr['application']->getProgram()->getName()} resulted in a mail exception: " . $e->getMessage());
        }
      }
      if ($count = count($applications)) {
        $cron->log("Sent {$count} reminder messages to programs.");
      }
    }
  }

  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null)
  {
    //all action authorizations are controlled by the index action
    $action = 'index';

    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}