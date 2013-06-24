<?php
namespace Jazzee\Page;

/**
 * Get recommender information from applicnats and send out invitations
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Recommenders extends AbstractPage implements \Jazzee\Interfaces\StatusPage
{
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
  protected function getMessage(\Jazzee\Entity\Answer $answer, $link)
  {
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
    if ($deadline = $this->_applicationPage->getPage()->getVar('lorDeadline')) {
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
    $message->AddCustomHeader('X-Jazzee-Applicant-ID:' . $this->_applicant->getId());
    $message->AddAddress(
        $this->_applicationPage->getPage()->getElementByFixedId(self::FID_EMAIL)->getJazzeeElement()->displayValue($answer),
        $this->_applicationPage->getPage()->getElementByFixedId(self::FID_FIRST_NAME)->getJazzeeElement()->displayValue($answer) . ' ' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_LAST_NAME)->getJazzeeElement()->displayValue($answer)
    );
    $message->setFrom($this->_applicant->getApplication()->getContactEmail(), $this->_applicant->getApplication()->getContactName());
    $message->Subject = 'Recommendation Request for ' . $this->_applicant->getFullName();
    $message->Body = $body;

    return $message;
  }

  /**
   * Send the invitaiton email
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   * @param integer $answerID
   * @param array $postData
   */
  public function do_sendEmail($answerId, $postData)
  {
    if ($answer = $this->_applicant->findAnswerById($answerId)) {
      if (!$answer->isLocked() OR (!$answer->getChildren()->count() AND $answer->getUpdatedAt()->diff(new \DateTime('now'))->days >= $answer->getPage()->getVar('lorWaitDays'))) {
        $message = $this->getMessage($answer, $this->_controller->absolutePath('lor/' . $answer->getUniqueId()));
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
  public function do_sendAdminInvitation($answerId, $postData)
  {
    $this->checkIsAdmin();
    if ($answer = $this->_applicant->findAnswerById($answerId)) {
      $path = $this->_controller->absolutePath('lor/' . $answer->getUniqueId());
      $link = str_ireplace('admin/', '', $path);
      $message = $this->getMessage($answer, $link);
      $form = new \Foundation\Form;
      $field = $form->newField();
      $field->setLegend('Send Invitation');
      $element = $field->newElement('Textarea', 'body');
      $element->setLabel('Email Text');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->setValue($message->Body);
      if (!empty($postData)) {
        if ($input = $form->processInput($postData)) {
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
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   * @param integer $answerID
   * @param array $postData
   */
  public function do_viewLink($answerId, $postData)
  {
    $this->checkIsAdmin();
    if ($answer = $this->_applicant->findAnswerById($answerId)) {
      $form = new \Foundation\Form;
      $field = $form->newField();
      $field->setLegend('Recommendation Link');
      $element = $field->newElement('Plaintext', 'link');
      if ($answer->isLocked()) {
        $path = $this->_controller->absolutePath('lor/' . $answer->getUniqueId());
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
  public function do_editLor($answerId, $postData)
  {
    $this->checkIsAdmin();
    if ($child = $this->_applicant->findAnswerById($answerId)->getChildren()->first()) {
      $lorPage = $child->getPage();
      $jazzeePage = $lorPage->getApplicationPageJazzeePage();
      $jazzeePage->setController($this->_controller);
      $jazzeePage->fillLorForm($child);
      $form = $jazzeePage->getForm();
      if (!empty($postData)) {
        if ($input = $jazzeePage->validateInput($postData)) {
          $jazzeePage->updateLorAnswer($input, $child);
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
  public function do_completeLor($answerId, $postData)
  {
    $this->checkIsAdmin();
    if ($answer = $this->_applicant->findAnswerById($answerId) and $answer->getChildren()->count() == 0) {
      $lorPage = $answer->getPage()->getChildren()->first();
      $jazzeePage = $lorPage->getApplicationPageJazzeePage();
      $jazzeePage->setController($this->_controller);
      $form = $jazzeePage->getForm();

      if (!empty($postData)) {
        if ($input = $jazzeePage->validateInput($postData)) {
          $jazzeePage->newLorAnswer($input, $answer);
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
  public function do_deleteLor($answerId)
  {
    $this->checkIsAdmin();
    if ($child = $this->_applicant->findAnswerById($answerId)->getChildren()->first()) {
      $lorPage = $child->getPage();
      $jazzeePage = $lorPage->getApplicationPageJazzeePage();
      $jazzeePage->setController($this->_controller);
      $jazzeePage->deleteLorAnswer($child);
      $this->_controller->setLayoutVar('status', 'success');
    } else {
      $this->_controller->setLayoutVar('status', 'error');
    }
  }

  /**
   * Create the recommenders form
   */
  public function setupNewPage()
  {
    $entityManager = $this->_controller->getEntityManager();
    $types = $entityManager->getRepository('Jazzee\Entity\ElementType')->findAll();
    $elementTypes = array();
    foreach ($types as $type) {
      $elementTypes[$type->getClass()] = $type;
    };
    $count = 1;
    foreach (array(self::FID_FIRST_NAME => 'First Name', self::FID_LAST_NAME => 'Last Name', self::FID_INSTITUTION => 'Institution') as $fid => $title) {
      $element = new \Jazzee\Entity\Element;
      $element->setType($elementTypes['\Jazzee\Element\TextInput']);
      $element->setTitle($title);
      $element->required();
      $element->setWeight($count);
      $element->setFixedId($fid);
      $this->_applicationPage->getPage()->addElement($element);
      $entityManager->persist($element);
      $count++;
    }

    $element = new \Jazzee\Entity\Element;
    $element->setType($elementTypes['\Jazzee\Element\EmailAddress']);
    $element->setTitle('Email Address');
    $element->required();
    $element->setWeight(5);
    $element->setFixedId(self::FID_EMAIL);
    $this->_applicationPage->getPage()->addElement($element);
    $entityManager->persist($element);

    $element = new \Jazzee\Entity\Element;
    $element->setType($elementTypes['\Jazzee\Element\Phonenumber']);
    $element->setTitle('Phone Number');
    $element->required();
    $element->setWeight(6);
    $element->setFixedId(self::FID_PHONE);
    $this->_applicationPage->getPage()->addElement($element);
    $entityManager->persist($element);

    $element = new \Jazzee\Entity\Element;
    $element->setType($elementTypes['\Jazzee\Element\RadioList']);
    $element->setTitle('Do you waive your right to view this letter at a later time?');
    $element->required();
    $element->setWeight(7);
    $element->setFixedId(self::FID_WAIVE_RIGHT);
    $this->_applicationPage->getPage()->addElement($element);
    $entityManager->persist($element);

    $item = new \Jazzee\Entity\ElementListItem;
    $item->setValue('Yes');
    $item->setWeight(1);
    $element->addItem($item);
    $entityManager->persist($item);

    $item = new \Jazzee\Entity\ElementListItem;
    $item->setValue('No');
    $item->setWeight(2);
    $element->addItem($item);
    $entityManager->persist($item);

    $defaultVars = array(
      'lorDeadline' => null,
      'lorDeadlineEnforced' => false,
      'recommenderEmailText' => '',
      'lorWaitDays' => 14
    );
    foreach ($defaultVars as $name => $value) {
      $var = $this->_applicationPage->getPage()->setVar($name, $value);
      $entityManager->persist($var);
    }
  }

  public function getStatus()
  {
    $answers = $this->getAnswers();
    if (!$this->_applicationPage->isRequired() and count($answers) and $answers[0]->getPageStatus() == self::SKIPPED) {
      return self::SKIPPED;
    }
    $completedAnswers = 0;
    foreach ($answers as $answer) {
      if ($answer->isLocked()) {
        $completedAnswers++;
      }
    }
    if (is_null($this->_applicationPage->getMin()) or $completedAnswers < $this->_applicationPage->getMin()) {
      return self::INCOMPLETE;
    } else {
      return self::COMPLETE;
    }
  }

  public function getArrayStatus(array $answers)
  {
    if (!$this->_applicationPage->isRequired() and count($answers) and $answers[0]['pageStatus'] == self::SKIPPED) {
      return self::SKIPPED;
    }
    $completedAnswers = 0;
    foreach ($answers as $answer) {
      if ($answer['locked']) {
        $completedAnswers++;
      }
    }
    if (is_null($this->_applicationPage->getMin()) or $completedAnswers < $this->_applicationPage->getMin()) {
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
  public function renderPdfSection(\Jazzee\ApplicantPDF $pdf)
  {
      $pdf->addText($this->_applicationPage->getTitle() . "\n", 'h3');
      foreach ($this->getAnswers() as $answer) {
        foreach ($this->_applicationPage->getPage()->getElements() as $element) {
          $element->getJazzeeElement()->setController($this->_controller);
          $value = $element->getJazzeeElement()->pdfValue($answer, $pdf);
          if(!empty($value)){
            $pdf->addText("{$element->getTitle()}: ", 'b');
            $pdf->addText("{$value}\n", 'p');
          }
        }
        if ($child = $answer->getChildren()->first()) {
          $jazzeePage = $this->_applicationPage->getPage()->getChildren()->first()->getApplicationPageJazzeePage();
          $jazzeePage->setApplicant($this->_applicant);
          $jazzeePage->setController($this->_controller);
          $jazzeePage->renderLorPdfAnswer($pdf, $this->_applicationPage->getPage()->getChildren()->first(), $child);
        }
        if(!$pdf instanceof \Jazzee\RestrictedPDF){
          if ($attachment = $answer->getAttachment()) {
            $pdf->addPdf($attachment->getAttachment());
          }
        }
        $pdf->addText("\n", 'p');
      }
  }

  public static function applyPageElement()
  {
    return 'Recommenders-apply_page';
  }

  public static function pageBuilderScriptPath()
  {
    return 'resource/scripts/page_types/JazzeePageRecommenders.js';
  }

  public static function applyStatusElement()
  {
    return 'Recommenders-apply_status';
  }

  public static function applicantsSingleElement()
  {
    return 'Recommenders-applicants_single';
  }

  /**
   * Check variables before they are set
   * @param string $name
   * @param string $value
   * @throws \Jazzee\Exception
   */
  public function setVar($name, $value)
  {
    switch ($name) {
      case 'lorDeadline':
        if (!empty($value)) {
          if (!$value = \strtotime($value)) {
            throw new \Jazzee\Exception("{$value} is not a valid date for lorDeadline");
          }
          $value = \date('Y-m-d H:i:s', $value);
        }
          break;
      case 'lorWaitDays':
        if (!empty($value)) {
          $value = (int) $value;
          if ($value < 0 OR $value > 100) {
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

  /**
   * Recommenders Pages include all child page elements of the recommendation
   * 
   * @return array
   */
  public function listDisplayElements()
  {
    $elements = parent::listDisplayElements();
    $weight = count($elements);
    foreach($this->_applicationPage->getPage()->getChildren() as $child){
      foreach($child->getApplicationPageJazzeePage()->listDisplayElements() as $displayElement){
        if($displayElement->type != 'page' and !in_array($displayElement->name, array('attachment', 'answerPublicStatus', 'answerPrivateStatus'))){
          $elements[] = new \Jazzee\Display\Element($displayElement->type, $this->_applicationPage->getTitle() . ' ' . $displayElement->title, $weight++, $displayElement->name, $displayElement->pageId);
        }
      }
    }
    $elements[] = new \Jazzee\Display\Element('page', 'Received Recommendations', $weight++, 'lorReceived', $this->_applicationPage->getPage()->getId());

    return $elements;
  }

}