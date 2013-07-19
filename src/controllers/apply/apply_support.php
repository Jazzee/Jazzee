<?php

/**
 * The suport portal allows applicants to ask, review, and respond to questions
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplySupportController extends \Jazzee\AuthenticatedApplyController
{

  public function beforeAction()
  {
    parent::beforeAction();
    $layoutContentTop = '<p class="links">';
    $layoutContentTop .= '<a href="' . $this->applyPath('account') . '">My Account</a>';
    $layoutContentTop .= '<a href="' . $this->applyPath('support') . '">Support</a>';
    if ($count = $this->_applicant->unreadMessageCount()) {
      $layoutContentTop .= '<sup class="count">' . $count . '</sup>';
    }
    $layoutContentTop .= '<a href="' . $this->applyPath('applicant/logout') . '">Log Out</a></p>';

    $this->setLayoutVar('layoutContentTop', $layoutContentTop);
  }

  /**
   * Display the page
   */
  public function actionIndex()
  {
    $this->setVar('threads', $this->_applicant->getThreads());
  }

  /**
   * Ask a new question
   */
  public function actionNew()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->applyPath('support/new'));
    $field = $form->newField();
    $field->setLegend('Ask a question');

    $element = $field->newElement('TextInput', 'subject');
    $element->setLabel('Subject');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));

    $element = $field->newElement('Textarea', 'text');
    $element->setLabel('Your Question');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    $form->newButton('submit', 'Submit');
    $this->setVar('form', $form);

    if ($input = $form->processInput($this->post)) {
      $thread = new \Jazzee\Entity\Thread();
      $thread->setSubject($input->get('subject'));
      $thread->setApplicant($this->_applicant);

      $message = new \Jazzee\Entity\Message();
      $message->setSender(\Jazzee\Entity\Message::APPLICANT);
      $message->setText($input->get('text'));
      $thread->addMessage($message);
      $this->_em->persist($thread);
      $this->_em->persist($message);
      $this->addMessage('success', 'Your message has been sent.');
      $this->redirectApplyPath('support');
    }
  }

  /**
   * Mark message as unread
   *
   */
  public function actionMarkUnread()
  {
    $message = $this->_em->getRepository('\Jazzee\Entity\Message')->find($this->actionParams['id']);
    if (!$message or $message->getThread()->getApplicant() != $this->_applicant) {
      throw new \Jazzee\Exception($this->actionParams['id'] . " is not a valid message for " . $this->_applicant->getId());
    }
    $message->unRead();
    $this->_em->persist($message);
    $this->addMessage('success', 'Message marked as unread');
    $this->redirectApplyPath('support');
  }

  /**
   * Reply to a message
   */
  public function actionReply()
  {
    $thread = $this->_em->getRepository('\Jazzee\Entity\Thread')->findOneBy(array('id' => $this->actionParams['id'], 'applicant' => $this->_applicant->getId()));
    if (!$thread) {
      throw new \Jazzee\Exception($this->actionParams['id'] . " is not a valid thread id for applicant " . $this->_applicant->getId());
    }
    $this->setVar('thread', $thread);

    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->applyPath('support/reply/' . $thread->getId()));

    $field = $form->newField();
    $field->setLegend('Reply to message');

    $element = $field->newElement('Textarea', 'text');
    $element->setLabel('Your Reply');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    $form->newButton('submit', 'Submit');
    $this->setVar('form', $form);

    if ($input = $form->processInput($this->post)) {
      $reply = new \Jazzee\Entity\Message();
      $reply->setSender(\Jazzee\Entity\Message::APPLICANT);
      $reply->setText($input->get('text'));
      $thread->addMessage($reply);
      $this->_em->persist($reply);
      $this->addMessage('success', 'Your reply has been sent.');
      $this->redirectApplyPath('support');
    }
  }

  /**
   * View a single message
   *
   */
  public function actionSingle()
  {
    $thread = $this->_em->getRepository('\Jazzee\Entity\Thread')->findOneBy(array('id' => $this->actionParams['id'], 'applicant' => $this->_applicant->getId()));
    if (!$thread) {
      throw new \Jazzee\Exception($this->actionParams['id'] . " is not a valid thread id for applicant " . $this->_applicant->getId());
    }
    $this->setVar('thread', $thread);
  }

  /**
   * Navigation
   * @return Navigation
   */
  public function getNavigation()
  {
    $navigation = new \Foundation\Navigation\Container();
    $menu = new \Foundation\Navigation\Menu();

    $menu->setTitle('Navigation');
    $link = new \Foundation\Navigation\Link('Back to Application');
    reset($this->_pages);
    $first = key($this->_pages);
    $link->setHref($this->applyPath('page/' . $first));
    $menu->addLink($link);
    $link = new \Foundation\Navigation\Link('Logout');
    $link->setHref($this->applyPath('applicant/logout'));
    $menu->addLink($link);

    $navigation->addMenu($menu);

    return $navigation;
  }

}