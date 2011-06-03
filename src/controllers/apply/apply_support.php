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
    $messages = $this->_applicant->getMessages();
    $this->setVar('messages', $messages);
  }
  
  /**
   * Ask a new question
   */
  public function actionNew() {
    $form = new \Foundation\Form();
    $form->setAction($this->path('apply/' .$this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/support/new'));
    $field = $form->newField();
    $field->setLegend('Ask a question');
    $element = $field->newElement('Textarea', 'text');
    $element->setLabel('Your Question');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $form->newButton('submit', 'Submit');
    $this->setVar('form', $form);
    
    if($input = $form->processInput($this->post)){
      $message = new \Jazzee\Entity\Message();
      $message->setApplicant($this->_applicant);
      $message->setSender('applicant');
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
    if(!$message) die;
    $this->setVar('message', $message);
    $form->setAction($this->path('apply/' .$this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/support/reply/' . $message->getId()));
    
    $field = $form->newField();
    $field->setLegend('Reply to message');
    $element = $field->newElement('Textarea', 'text');
    $element->setLabel('Your Reply');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $form->newButton('submit', 'Submit');
    $this->setVar('form', $form);
    
    if($input = $form->processInput($this->post)){
      $reply = new \Jazzee\Entity\Message();
      $reply->setApplicant($this->_applicant);
      $reply->setSender('applicant');
      $message->addReply($reply);
      $reply->setText($input->get('text'));
      $this->_em->persist($reply);
      $this->addMessage('success', 'Your reply has been sent.');
      $this->redirectPath('apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/support');
    }
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
    $link->setHref($this->path($path . '/page/' . $this->_application->getPages()->first()->getId()));
    $menu->addLink($link); 
    $link = new \Foundation\Navigation\Link('Logout');
    $link->setHref($this->path('apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/applicant/logout'));
    $menu->addLink($link);
    
    $navigation->addMenu($menu);
    return $navigation;
  }
}
?>
