<?php
namespace Jazzee\Entity\Page;
/**
 * Get recommender information from applicnats and send out invitations
 */
class Recommenders extends Standard {
  
  /**
   * The time to wait between sending emails to recommenders in days
   * @cons integer 14 days
   */
  const RECOMMENDATION_EMAIL_WAIT_DAYS = 14;
  
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
      if(!$answer->isLocked() OR (!$answer->getChildren()->count() AND $answer->getUpdatedAt()->diff(new \DateTime('now'))->days >= self::RECOMMENDATION_EMAIL_WAIT_DAYS)){
        $search = array(
         '%APPLICANT_NAME%',
         '%DEADLINE%',
         '%LINK%',
         '%RECOMMENDER_FIRST_NAME%',
         '%RECOMMENDER_LAST_NAME%',
         '%RECOMMENDER_INSTITUTION%',
         '%RECOMMENDER_EMAIL%',
         '%RECOMMENDER_PHONE%',
         '%APPLICANT_WAIVE_RIGHT%'
        );
        if($deadline = $this->_applicationPage->getPage()->getVar('lorDeadline')){
          $deadline = new \DateTime($deadline);
        } else {
          $deadline = $this->_applicant->getApplication()->getClose();
        }
        $replace = array(
         $this->_applicant->getFullName(),
         $deadline->format('l F jS Y g:ia'),
         $this->_controller->path('lor/' . $answer->getUniqueId())
        );
        $replace[] = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_FIRST_NAME)->getJazzeeElement()->displayValue($answer);
        $replace[] = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_LAST_NAME)->getJazzeeElement()->displayValue($answer);
        $replace[] = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_INSTITUTION)->getJazzeeElement()->displayValue($answer);
        $replace[] = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_EMAIL)->getJazzeeElement()->displayValue($answer);
        $replace[] = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_PHONE)->getJazzeeElement()->displayValue($answer);
        $replace[] = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_WAIVE_RIGHT)->getJazzeeElement()->displayValue($answer);
        $body = str_ireplace($search, $replace, $this->_applicationPage->getPage()->getVar('recommenderEmailText'));

//        $this->_controller->sendEmail(
//          $this->_applicationPage->getPage()->getElementByFixedId(self::FID_EMAIL)->getJazzeeElement()->displayValue($answer),
//          $this->_applicationPage->getPage()->getElementByFixedId(self::FID_FIRST_NAME)->getJazzeeElement()->displayValue($answer) . ' ' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_LAST_NAME)->getJazzeeElement()->displayValue($answer), 
//          $this->_applicant->getApplication()->getContactEmail(),
//          $this->_applicant->getApplication()->getContactName(),
//          'Letter of Recommendation Request', 
//          $body
//        );
        $answer->lock();
        $answer->markLastUpdate();
        $this->_controller->getEntityManager()->persist($answer);
        $this->_controller->addMessage('success', 'Your invitation was sent successfully.');
      }
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
}