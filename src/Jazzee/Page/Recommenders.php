<?php
namespace Jazzee\Page;
/**
 * Get recommender information from applicnats and send out invitations
 * 
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage pages
 */
class Recommenders extends Standard {
  const APPLY_PAGE_ELEMENT = 'Recommenders-apply_page';
  const APPLICANTS_SINGLE_ELEMENT = 'Recommenders-applicants_single';
  const APPLY_STATUS_ELEMENT = 'Recommenders-apply_status';
  const PAGEBUILDER_SCRIPT = 'resource/scripts/page_types/JazzeePageRecommenders.js';
  
  /**
   * These fixedIDs make it easy to find the element we are looking for
   * @const integer
   */
  const FID_FIRST_NAME = 2;
  const FID_LAST_NAME = 4;
  const FID_INSTITUTION = 6;
  const FID_EMAIL = 8;
  const FID_PHONE = 10;
  const FID_WAIVE_RIGHT = 12;
  
  /**
   * Get the message
   * @param \Jazzee\Entity\Answer $answer
   * @param string $link is different from admin and apply so it is sent as a parameter
   * @return \Foundation\Mail\Message
   */
  protected function getMessage(\Jazzee\Entity\Answer $answer, $link){
    $search = array(
     '_APPLICANT_NAME_',
     '_DEADLINE_',
     '_LINK_',
     '_RECOMMENDER_FIRST_NAME_',
     '_RECOMMENDER_LAST_NAME_',
     '_RECOMMENDER_INSTITUTION_',
     '_RECOMMENDER_EMAIL_',
     '_RECOMMENDER_PHONE_',
     '_APPLICANT_WAIVE_RIGHT_'
    );
    if($deadline = $this->_applicationPage->getPage()->getVar('lorDeadline')){
      $deadline = new \DateTime($deadline);
    } else {
      $deadline = $this->_applicant->getApplication()->getClose();
    }
    $replace = array(
     $this->_applicant->getFullName(),
     $deadline->format('l F jS Y g:ia'),
     $link
    );
    $replace[] = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_FIRST_NAME)->getJazzeeElement()->displayValue($answer);
    $replace[] = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_LAST_NAME)->getJazzeeElement()->displayValue($answer);
    $replace[] = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_INSTITUTION)->getJazzeeElement()->displayValue($answer);
    $replace[] = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_EMAIL)->getJazzeeElement()->displayValue($answer);
    $replace[] = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_PHONE)->getJazzeeElement()->displayValue($answer);
    $replace[] = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_WAIVE_RIGHT)->getJazzeeElement()->displayValue($answer);
    $body = str_ireplace($search, $replace, $this->_applicationPage->getPage()->getVar('recommenderEmailText'));

    $message = $this->_controller->newMailMessage();
    $message->AddAddress(
      $this->_applicationPage->getPage()->getElementByFixedId(self::FID_EMAIL)->getJazzeeElement()->displayValue($answer),
      $this->_applicationPage->getPage()->getElementByFixedId(self::FID_FIRST_NAME)->getJazzeeElement()->displayValue($answer) . ' ' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_LAST_NAME)->getJazzeeElement()->displayValue($answer));
    $message->setFrom($this->_applicant->getApplication()->getContactEmail(), $this->_applicant->getApplication()->getContactName());
    $message->Subject = 'Letter of Recommendation Request';
    $message->Body = $body;
    return $message;
  }
  
  /**
   * Send the invitaiton email
   * @param integer $answerID
   * @param array $postData
   */
  public function do_sendEmail($answerId, $postData){
    if($answer = $this->_applicant->findAnswerById($answerId)){
      if(!$answer->isLocked() OR (!$answer->getChildren()->count() AND $answer->getUpdatedAt()->diff(new \DateTime('now'))->days >= $answer->getPage()->getVar('lorWaitDays'))){
        $message = $this->getMessage($answer, $this->_controller->path('lor/' . $answer->getUniqueId()));
        $message->Send();
        $answer->lock();
        $answer->markLastUpdate();
        $this->_controller->getEntityManager()->persist($answer);
        $this->_controller->addMessage('success', 'Your invitation was sent successfully.');
      }
    }
  }
  

  
  /**
   * Send the invitaiton email
   * @param integer $answerID
   * @param array $postData
   */
  public function do_sendAdminInvitation($answerId, $postData){
    $this->checkIsAdmin();
    if($answer = $this->_applicant->findAnswerById($answerId)){
      $path = $this->_controller->path('lor/' . $answer->getUniqueId());
      $link = str_ireplace('admin/', '', $path);
      $message = $this->getMessage($answer, $link);
      $form = new \Foundation\Form;
      $field = $form->newField();
      $field->setLegend('Send Invitation');
      $element = $field->newElement('Textarea', 'body');
      $element->setLabel('Email Text');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->setValue($message->Body);
      if(!empty($postData)){
        if($input = $form->processInput($postData)){
          $message->Body = $input->get('body');
          $message->Send();
          $answer->lock();
          $answer->markLastUpdate();
          $this->_controller->getEntityManager()->persist($answer);
          $this->_controller->setLayoutVar('status', 'success');
        } else {
          $this->_controller->setLayoutVar('status', 'error');
        }
      }
      $form->newButton('submit', 'Send Email');
      return $form;
    }
    return false;
  }
  
  /**
   * View the recommendation Link
   * Admin feature to display the link that recommenders are emailed
   * @param integer $answerID
   * @param array $postData
   */
  public function do_viewLink($answerId, $postData){
    $this->checkIsAdmin();
    if($answer = $this->_applicant->findAnswerById($answerId)){
      $form = new \Foundation\Form;
      $field = $form->newField();
      $field->setLegend('Recommendation Link');
      $element = $field->newElement('Plaintext', 'link');
      if($answer->isLocked()){
        $path = $this->_controller->path('lor/' . $answer->getUniqueId());
        $path = str_ireplace('admin/', '', $path);
        $element->setValue($path);
      } else {
        $element->setValue('No link is available until an invitation has been sent.');
      }
      $this->_controller->setLayoutVar('status', 'success');
      return $form;
    }
    $this->_controller->setLayoutVar('status', 'error');
  }
  
  /**
   * Edit a submitted recommendation
   * Admin feature to edit a submitted recommendation
   * @param integer $answerID
   * @param array $postData
   */
  public function do_editLor($answerId, $postData){
    $this->checkIsAdmin();
    if($child = $this->_applicant->findAnswerById($answerId)->getChildren()->first()){
      $lorPage = $child->getPage();
      $jp = $lorPage->getApplicationPageJazzeePage();
      $jp->setController($this->_controller);
      $jp->fillLorForm($child);
      $form = $jp->getForm();
      if(!empty($postData)){
        if($input = $jp->validateInput($postData)){
          $jp->updateLorAnswer($input, $child);
          $this->_controller->setLayoutVar('status', 'success');
        } else {
          $this->_controller->setLayoutVar('status', 'error');
        }
      }
      return $form;
    }
    $this->_controller->setLayoutVar('status', 'error');
  }
  
  /**
   * Complete the recommendation
   * Admin feature to complete a recommendation
   * @param integer $answerID
   * @param array $postData
   */
  public function do_completeLor($answerId, $postData){
    $this->checkIsAdmin();
    if($answer = $this->_applicant->findAnswerById($answerId) and $answer->getChildren()->count() == 0){
      $lorPage = $answer->getPage()->getChildren()->first();
      $jp = $lorPage->getApplicationPageJazzeePage();
      $jp->setController($this->_controller);
      $form = $jp->getForm();
      
      if(!empty($postData)){
        if($input = $jp->validateInput($postData)){
          $jp->newLorAnswer($input, $answer);
          $this->_controller->setLayoutVar('status', 'success');
        } else {
          $this->_controller->setLayoutVar('status', 'error');
        }
      }
      
      return $form;
    }
    $this->_controller->setLayoutVar('status', 'error');
  }
  
  /**
   * Delete a submitted recommendation
   * Admin feature to delete a submitted recommendation
   * @param integer $answerID
   */
  public function do_deleteLor($answerId){
    $this->checkIsAdmin();
    if($child = $this->_applicant->findAnswerById($answerId)->getChildren()->first()){
      $lorPage = $child->getPage();
      $jp = $lorPage->getApplicationPageJazzeePage();
      $jp->setController($this->_controller);
      $jp->deleteLorAnswer($child);
      $this->_controller->setLayoutVar('status', 'success');
    } else {
      $this->_controller->setLayoutVar('status', 'error');
    }
  }
  
  /**
   * Create the recommenders form
   */
  public function setupNewPage(){
    $em = $this->_controller->getEntityManager();
    $types = $em->getRepository('Jazzee\Entity\ElementType')->findAll();
    $elementTypes = array();
    foreach($types as $type){
      $elementTypes[$type->getClass()] = $type;
    };
    $count = 1;
    foreach(array(self::FID_FIRST_NAME=>'First Name',self::FID_LAST_NAME=>'Last Name',self::FID_INSTITUTION=>'Institution') as $fid => $title){
      $element = new \Jazzee\Entity\Element;
      $element->setType($elementTypes['\Jazzee\Element\TextInput']);
      $element->setTitle($title);
      $element->required();
      $element->setWeight($count);
      $element->setFixedId($fid);
      $this->_applicationPage->getPage()->addElement($element);
      $em->persist($element);
      $count++;
    }
    
    $element = new \Jazzee\Entity\Element;
    $element->setType($elementTypes['\Jazzee\Element\EmailAddress']);
    $element->setTitle('Email Address');
    $element->required();
    $element->setWeight(5);
    $element->setFixedId(self::FID_EMAIL);
    $this->_applicationPage->getPage()->addElement($element);
    $em->persist($element);
    
    $element = new \Jazzee\Entity\Element;
    $element->setType($elementTypes['\Jazzee\Element\Phonenumber']);
    $element->setTitle('Phone Number');
    $element->required();
    $element->setWeight(6);
    $element->setFixedId(self::FID_PHONE);
    $this->_applicationPage->getPage()->addElement($element);
    $em->persist($element);
    
    $element = new \Jazzee\Entity\Element;
    $element->setType($elementTypes['\Jazzee\Element\RadioList']);
    $element->setTitle('Do you waive your right to view this letter at a later time?');
    $element->required();
    $element->setWeight(7);
    $element->setFixedId(self::FID_WAIVE_RIGHT);
    $this->_applicationPage->getPage()->addElement($element);
    $em->persist($element);
    
    $item = new \Jazzee\Entity\ElementListItem;
    $item->setValue('Yes');
    $item->setWeight(1);
    $element->addItem($item);
    $em->persist($item);
    
    $item = new \Jazzee\Entity\ElementListItem;
    $item->setValue('No');
    $item->setWeight(2);
    $element->addItem($item);
    $em->persist($item);
    
    $defaultVars = array(
      'lorDeadline' => null,
      'lorDeadlineEnforced' => false,
      'recommenderEmailText' => '',
      'lorWaitDays' => 14
    );
    foreach($defaultVars as $name=>$value){
      $var = $this->_applicationPage->getPage()->setVar($name, $value);
      $em->persist($var);
    }    
  }
  
  public function getStatus(){
    $answers = $this->getAnswers();
    if(!$this->_applicationPage->isRequired() and count($answers) and $answers[0]->getPageStatus() == self::SKIPPED){
      return self::SKIPPED;
    }
    $completedAnswers = 0;
    foreach($answers as $answer)if($answer->isLocked()) $completedAnswers++;
    
    if(is_null($this->_applicationPage->getMin()) or $completedAnswers < $this->_applicationPage->getMin()){
      return self::INCOMPLETE;
    } else {
      return self::COMPLETE;
    }
  }
  
  /**
   * Create a table from answers
   * and append any attached PDFs
   * @param \Jazzee\ApplicantPDF $pdf 
   */
  public function renderPdfSection(\Jazzee\ApplicantPDF $pdf){
    if($this->getAnswers()){
      $pdf->addText($this->_applicationPage->getTitle(), 'h3');
      $pdf->write();
      $pdf->startTable();
      $pdf->startTableRow();
      $pdf->addTableCell('Recommender');
      foreach($this->_applicationPage->getPage()->getChildren()->first()->getElements() as $element)$pdf->addTableCell($element->getTitle());
      foreach($this->getAnswers() as $answer){
        $pdf->startTableRow();
        $string = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_FIRST_NAME)->getJazzeeElement()->pdfValue($answer, $pdf) . "\n";
        $string .= $this->_applicationPage->getPage()->getElementByFixedId(self::FID_LAST_NAME)->getJazzeeElement()->pdfValue($answer, $pdf) . "\n";
        $string .= $this->_applicationPage->getPage()->getElementByFixedId(self::FID_INSTITUTION)->getJazzeeElement()->pdfValue($answer, $pdf) . "\n";
        $string .= $this->_applicationPage->getPage()->getElementByFixedId(self::FID_EMAIL)->getJazzeeElement()->pdfValue($answer, $pdf) . "\n";
        $string .= $this->_applicationPage->getPage()->getElementByFixedId(self::FID_PHONE)->getJazzeeElement()->pdfValue($answer, $pdf) . "\n";
        $pdf->addTableCell($string);
        if($child = $answer->getChildren()->first()){
          foreach($this->_applicationPage->getPage()->getChildren()->first()->getElements() as $element){
            $element->getJazzeeElement()->setController($this->_controller);
            $pdf->addTableCell($element->getJazzeeElement()->pdfValue($child, $pdf));
          }
        }
        if($attachment = $answer->getAttachment()) $pdf->addPdf($attachment->getAttachment());
      }
      $pdf->writeTable();
    }
  }
  
  /**
   * Check variables before they are set
   * @param string $name
   * @param string $value
   * @throws \Jazzee\Exception 
   */
  public function setVar($name, $value){
    switch($name){
      case 'lorDeadline':
          if(!empty($value)){
            if(!$value = \strtotime($value)){
              throw new \Jazzee\Exception("{$value} is not a valid date for lorDeadline");
            }
            $value = \date('Y-m-d H:i:s', $value);
          }
        break;
      case 'lorWaitDays':
        if(!empty($value)){
          $value = (int)$value;
          if($value < 0 OR $value > 100){
            throw new \Jazzee\Exception("lorWaitDays should be between 0 and 100.  {$value} is not.");
          }
        }
        break;
      case 'lorDeadlineEnforced':
        break;
      case 'recommenderEmailText':
        break;
      default:
        throw new \Jazzee\Exception($name . ' is not a valid variable on this page.');
    }
    parent::setVar($name, $value);
  }
}