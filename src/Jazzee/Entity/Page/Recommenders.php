<?php
namespace Jazzee\Entity\Page;
/**
 * Get recommender information from applicnats and send out invitations
 */
class Recommenders extends Standard {
  /**
   * The answer class for this page type
   * @const string
   */
  const ANSWER_CLASS = '\Jazzee\Entity\Answer\Recommenders';
  /**
   * The time to wait between sending emails to recommenders in seconds
   * @cons integer 2 weeks (86400 * 14)
   */
  const RECOMMENDATION_EMAIL_WAIT_TIME = 1209600;
  
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
   * Send the invitaiton email
   * @param integer $answerID
   * @param array $postData
   */
  public function sendEmail($answerId, $postData){
    if($answer = $this->_applicant->findAnswerById($answerId)){
      if(!$answer->isLocked() OR (!$answer->getChildren()->count() AND time() - $answer->getUpdatedAt()->format('U') > self::RECOMMENDATION_EMAIL_WAIT_TIME)){
        $this->_controller->addMessage('info', 'Need to setup email sending - but will lock app');
        $answer->lock();
        $answer->markLastUpdate();
        $this->_controller->getEntityManager()->persist($answer);
      }
    }
  }
  /*
public function sendEmail(){
    $mail = JazzeeMail::getInstance();
    $search = array(
     '%APPLICANT_NAME%',
     '%DEADLINE%',
     '%LINK%',
     '%PROGRAM_CONTACT_NAME%',
     '%PROGRAM_CONTACT_EMAIL%',
     '%PROGRAM_CONTACT_PHONE%',
     '%RECOMMENDER_FIRST_NAME%',
     '%RECOMMENDER_LAST_NAME%',
     '%RECOMMENDER_INSTITUTION%',
     '%RECOMMENDER_EMAIL%',
     '%RECOMMENDER_PHONE%',
     '%APPLICANT_WAIVE_RIGHT%'
    );
    if($this->answer->Page->getVar('lorDeadline')){
      $deadline = strtotime($this->answer->Page->getVar('lorDeadline'));
    } else {
      $deadline = strtotime($this->answer->Applicant->Application->close);
    }
    $replace = array(
     "{$this->answer->Applicant->firstName} {$this->answer->Applicant->lastName}",
     date('l F jS Y g:ia', $deadline),
     $mail->path('lor/' . $this->answer->uniqueID),
     $this->answer->Applicant->Application->contactName,
     $this->answer->Applicant->Application->contactEmail,
     $this->answer->Applicant->Application->contactPhone
    );
    $replace[] = $this->getDisplayValueForFixedElement(RecommendersPage::FID_FIRST_NAME);
    $replace[] = $this->getDisplayValueForFixedElement(RecommendersPage::FID_LAST_NAME);
    $replace[] = $this->getDisplayValueForFixedElement(RecommendersPage::FID_INSTITUTION);
    $replace[] = $this->getDisplayValueForFixedElement(RecommendersPage::FID_EMAIL);
    $replace[] = $this->getDisplayValueForFixedElement(RecommendersPage::FID_PHONE);
    $replace[] = $this->getDisplayValueForFixedElement(RecommendersPage::FID_WAIVE_RIGHT);
    $text = str_ireplace($search, $replace, $this->answer->Page->getVar('recommenderEmailText'));

    $message = new EmailMessage;
    $message->to($this->getDisplayValueForFixedElement(RecommendersPage::FID_EMAIL), '');
    $message->from($this->answer->Applicant->Application->contactEmail, $this->answer->Applicant->Application->contactName);
    $message->subject = 'Letter of Recommendation Request';
    $message->body = $text;
    if(!$mail->send($message)){
      return false;
    }
    $this->answer->locked = true;
    $this->answer->save();
    return true;
  }*/
  
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
    foreach(array(self::FID_FIRST_NAME=>'First Name',self::FID_LAST_NAME=>'Last Name',self::FID_INSTITUTION=>'Institution',self::FID_EMAIL=>'Email Address',self::FID_PHONE=>'Phone Number') as $fid => $title){
      $element = new \Jazzee\Entity\Element;
      $element->setType($elementTypes['\\Jazzee\\Entity\Element\TextInput']);
      $element->setTitle($title);
      $element->required();
      $element->setWeight($count);
      $element->setFixedId($fid);
      $this->_applicationPage->getPage()->addElement($element);
      $em->persist($element);
      $count++;
    }
    $element = new \Jazzee\Entity\Element;
    $element->setType($elementTypes['\\Jazzee\\Entity\Element\RadioList']);
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
  }
}