<?php
/**
 * The suport portal allows applicants to ask, review, and respond to questions
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage apply
 */
class ApplySupportController extends \Jazzee\ApplyController {  
  
  /**
   * Display the page
   */
  public function actionIndex() {
    $threads = $this->_em->getRepository('\Jazzee\Entity\Message')->findBy(array('applicant'=>$this->_applicant->getId(), 'parent'=>null));
    $this->setVar('threads', $threads);
  }
  
  /**
   * Ask a new question
   */
  public function actionNew() {
    $form = new \Foundation\Form();
    $form->setAction($this->path('apply/' .$this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/support/new'));
    $field = $form->newField();
    $field->setLegend('Ask a question');
    
    $element = $field->newElement('TextInput', 'subject');
    $element->setLabel('Subject');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('Textarea', 'text');
    $element->setLabel('Your Question');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $form->newButton('submit', 'Submit');
    $this->setVar('form', $form);
    
    if($input = $form->processInput($this->post)){
      $message = new \Jazzee\Entity\Message();
      $message->setApplicant($this->_applicant);
      $message->setSender(\Jazzee\Entity\Message::APPLICANT);
      $message->setSubject($input->get('subject'));
      $message->setText($input->get('text'));
      $this->_em->persist($message);
      $this->addMessage('success', 'Your message has been sent.');
      $this->redirectPath('apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/support');
    }
  }
  
  /**
   * Reply to a message
   */
  public function actionReply() {
    $form = new \Foundation\Form();
    $message = $this->_em->getRepository('\Jazzee\Entity\Message')->findOneBy(array('id'=>$this->actionParams['messageId'], 'applicant'=>$this->_applicant->getId()));
    if(!$message) throw new \Jazzee\Exception("{$messageId} is not a valid message id for applicant " . $this->_applicant->getId());
    $message = $message->getLastMessage();
    $this->setVar('thread', $message->getFirstMessage());
    $form->setAction($this->path('apply/' .$this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/support/reply/' . $message->getId()));
    
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
      $reply->setApplicant($this->_applicant);
      $reply->setSender(\Jazzee\Entity\Message::APPLICANT);
      $message->setReply($reply);
      $reply->setSubject($input->get('subject'));
      $reply->setText($input->get('text'));
      $this->_em->persist($reply);
      $this->addMessage('success', 'Your reply has been sent.');
      $this->redirectPath('apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/support');
    }
  }
  
  /**
   * View a single message
   * 
   */
  public function actionSingle() {
    $message = $this->_em->getRepository('\Jazzee\Entity\Message')->findOneBy(array('id'=>$this->actionParams['messageId'], 'applicant'=>$this->_applicant->getId()));
    if(!$message) throw new \Jazzee\Exception("{$messageId} is not a valid message id for applicant " . $this->_applicant->getId());
    $this->setVar('message', $message);
  }
  
  /**
   * Navigation
   * @return Navigation
   */
  public function getNavigation(){
    $navigation = new \Foundation\Navigation\Container();
    $menu = new \Foundation\Navigation\Menu();
    
    $menu->setTitle('Navigation');
    $path = 'apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName();
    $link = new \Foundation\Navigation\Link('Back to Application');
    reset($this->_pages);
    $first = key($this->_pages);
    $link->setHref($this->path($path . '/page/' . $first));
    $menu->addLink($link); 
    $link = new \Foundation\Navigation\Link('Logout');
    $link->setHref($this->path('apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/applicant/logout'));
    $menu->addLink($link);
    
    $navigation->addMenu($menu);
    return $navigation;
  }
}
?>
