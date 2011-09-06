<?php
namespace Jazzee\Entity\Page;
/**
 * The ETSMatch Application Page
 */
class ETSMatch extends Standard {
  /**
   * These fixedIDs make it easy to find the element we are looking for
   * @const integer
   */
  const FID_TEST_TYPE = 2;
  const FID_REGISTRATION_NUMBER = 4;
  const FID_TEST_DATE = 6;
  
  /**
   * Try and match a score to an answer
   * 
   * @param \Jazzee\Entity\Answer
   */
  public function matchScore(\Jazzee\Entity\Answer $answer){
    if($answer->getPageStatus() == self::SKIPPED) return;
    if(!is_null($answer->getGREScore()) and !is_null($answer->getTOEFLScore())) return; //we already have a match
    $testType = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_TEST_TYPE)->getJazzeeElement()->displayValue($answer);
    $registrationNumber = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_REGISTRATION_NUMBER)->getJazzeeElement()->displayValue($answer);
    $testDate = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_TEST_DATE)->getJazzeeElement()->formValue($answer);
    $testMonth = date('m', strtotime($testDate));
    $testYear = date('Y', strtotime($testDate));
    
    $parameters = array(
      'registrationNumber' => $registrationNumber,
      'testMonth' => $testMonth,
      'testYear' => $testYear
    );
    switch($testType){
      case 'GRE/GRE Subject':
          $score = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\GREScore')->findOneBy($parameters);
          if($score) $answer->setGreScore($score);
        break;
      case 'TOEFL':
          $score = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\TOEFLScore')->findOneBy($parameters);
          if($score) $answer->setTOEFLScore($score);
        break;
      default:
        throw new \Jazzee\Exception("Unknown test type: {$testType} when trying to match a score");
    }
    
  }
  
  public function newAnswer($input){
    $e = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_REGISTRATION_NUMBER);
    //trim leading zeros from Registration Number
    $input->set('el'.$e->getId(), ltrim($input->get('el'.$e->getId()), '0'));
    parent::newAnswer($input);
    //attempt to match any scores
    foreach($this->getAnswers() as $answer) $this->matchScore($answer);
  }
  
  public function updateAnswer($input, $answerId){
    $e = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_REGISTRATION_NUMBER);
    //trim leading zeros from Registration Number
    $input->set('el'.$e->getId(), ltrim($input->get('el'.$e->getId()), '0'));
    parent::updateAnswer($input, $answerId);
    //attempt to match any scores
    foreach($this->getAnswers() as $answer) $this->matchScore($answer);
  }
  
  /**
   * Find Possible gre score matches
   * @param integer $answerID
   * @param array $postData
   * @param bool $bool third required argument for admin functions to be sure they aren't called from the applicant side
   */
  public function findScores($postData, $bool){
    $form = new \Foundation\Form;
    $field = $form->newField();
    $field->setLegend('Find Matching Scores');
    $element = $field->newElement('CheckboxList', 'greMatches');
    $element->setLabel('Possible GRE');
    foreach($this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\GREScore')->findByName(substr($this->_applicant->getFirstName(), 0, 1) . '%', substr($this->_applicant->getLastName(), 0, 2) . '%') as $score){
      $element->newItem($score->getId(), $score->getLastName() . ',  ' . $score->getFirstName() . ' ' . $score->getMiddleInitial() . ' ' . $score->getTestDate()->format('m/d/Y'));
    }
    
    $element = $field->newElement('CheckboxList', 'toeflMatches');
    $element->setLabel('Possible TOEFL');
    foreach($this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\TOEFLScore')->findByName(substr($this->_applicant->getFirstName(), 0, 1) . '%', substr($this->_applicant->getLastName(), 0, 2) . '%') as $score){
      $element->newItem($score->getId(), $score->getLastName() . ',  ' . $score->getFirstName() . ' ' . $score->getMiddleName() . ' ' . $score->getTestDate()->format('m/d/Y'));
    }
    $form->newButton('submit', 'Match Scores');
    if(!empty($postData)){
      //create a blank for so it can get values from parent::newAnswer
      $this->_form = new \Foundation\Form();
      if($input = $form->processInput($postData)){
        if($input->get('greMatches')){
          foreach($input->get('greMatches') as $scoreId){
            $score = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\GREScore')->find($scoreId);
            $arr = array(
              'el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_REGISTRATION_NUMBER)->getId() => $score->getRegistrationNumber(),
              'el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_TEST_DATE)->getId() => $score->getTestDate()->format('c'),
              'el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_TEST_TYPE)->getId() => $this->_applicationPage->getPage()->getElementByFixedId(self::FID_TEST_TYPE)->getItemByValue('GRE/GRE Subject')->getId()
            );
            $newInput = new \Foundation\Form\Input($arr);
            $this->newAnswer($newInput);
          }
        }
        if($input->get('toeflMatches')){
          foreach($input->get('toeflMatches') as $scoreId){
            $score = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\TOEFLScore')->find($scoreId);
            $arr = array(
              'el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_REGISTRATION_NUMBER)->getId() => $score->getRegistrationNumber(),
              'el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_TEST_DATE)->getId() => $score->getTestDate()->format('c'),
              'el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_TEST_TYPE)->getId() => $this->_applicationPage->getPage()->getElementByFixedId(self::FID_TEST_TYPE)->getItemByValue('TOEFL')->getId()
            );
            $newInput = new \Foundation\Form\Input($arr);
            $this->newAnswer($newInput);
          }
        }
        $this->_controller->setLayoutVar('status', 'success');
      } else {
        $this->_controller->setLayoutVar('status', 'error');
      }
    }
    return $form;
  }
  
/**
   * Create the ets match form
   * @param Entity\Page $page
   */
  public function setupNewPage(){
    $em = $this->_controller->getEntityManager();
    $types = $em->getRepository('\Jazzee\Entity\ElementType')->findAll();
    $elementTypes = array();
    foreach($types as $type){
      $elementTypes[$type->getClass()] = $type;
    };
    $count = 1;

    $element = new \Jazzee\Entity\Element;
    $this->_applicationPage->getPage()->addElement($element);
    $element->setType($elementTypes['\\Jazzee\Entity\Element\RadioList']);
    $element->setTitle('Test Type');
    $element->required();
    $element->setWeight(1);
    $element->setFixedId(self::FID_TEST_TYPE);
    $em->persist($element);
    
    $item = new \Jazzee\Entity\ElementListItem;
    $element->addItem($item);
    $item->setValue('GRE/GRE Subject');
    $item->setWeight(1);
    $em->persist($item);
    
    $item = new \Jazzee\Entity\ElementListItem;
    $element->addItem($item);
    $item->setValue('TOEFL');
    $item->setWeight(2);
    $em->persist($item);
    
    $element = new \Jazzee\Entity\Element;
    $this->_applicationPage->getPage()->addElement($element);
    $element->setType($elementTypes['\\Jazzee\Entity\Element\TextInput']);
    $element->setTitle('ETS Registration Number');
    $element->setFormat('no leading zeros');
    $element->required();
    $element->setWeight(2);
    $element->setFixedId(self::FID_REGISTRATION_NUMBER);
    $em->persist($element);
    
    $element = new \Jazzee\Entity\Element;
    $this->_applicationPage->getPage()->addElement($element);
    $element->setType($elementTypes['\\Jazzee\Entity\Element\ShortDate']);
    $element->setTitle('Test Date');
    $element->required();
    $element->setWeight(3);
    $element->setFixedId(self::FID_TEST_DATE);
    $em->persist($element);
  }
  
  /**
   * Match unmatched scores as a cron task
   * @todo eventually the 1000 limit is going to block on dead scores - find a way to eventually avoid those or to keep looping in 1000 increments
   * @param AdminCronController $cron
   */
  public static function runCron(\AdminCronController $cron){
    $pageType = $cron->getEntityManager()->getRepository('\Jazzee\Entity\PageType')->findOneBy(array('class'=>'\Jazzee\Entity\Page\ETSMatch'));
    $allETSMatchPages = $cron->getEntityManager()->getRepository('\Jazzee\Entity\Page')->findBy(array('type'=>$pageType->getId()));
    foreach($allETSMatchPages as $page){
      //get all the answers without a matching score.  Limit this to 1000 per cron run to avoid memory time issues run them reverse cronologically so hopefully we don't hit a block with unmatched scores
      $answers = $cron->getEntityManager()->getRepository('\Jazzee\Entity\Answer')->findBy(array('page'=>$page->getId(), 'greScore'=>null, 'toeflScore'=>null),array('updatedAt'=>'desc'), 1000);
      foreach($answers as $answer){
        if(is_null($answer->getGREScore()) and is_null($answer->getTOEFLScore())){
          $testType = $page->getElementByFixedId(self::FID_TEST_TYPE)->getJazzeeElement()->displayValue($answer);
          $registrationNumber = $page->getElementByFixedId(self::FID_REGISTRATION_NUMBER)->getJazzeeElement()->displayValue($answer);
          $testDate = $page->getElementByFixedId(self::FID_TEST_DATE)->getJazzeeElement()->formValue($answer);
          $testMonth = date('m', strtotime($testDate));
          $testYear = date('Y', strtotime($testDate));
          
          $parameters = array(
            'registrationNumber' => $registrationNumber,
            'testMonth' => $testMonth,
            'testYear' => $testYear
          );
          switch($testType){
            case 'GRE/GRE Subject':
                $score = $cron->getEntityManager()->getRepository('\Jazzee\Entity\GREScore')->findOneBy($parameters);
                if($score) $answer->setGreScore($score);
                $cron->getEntityManager()->persist($answer);
              break;
            case 'TOEFL':
                $score = $cron->getEntityManager()->getRepository('\Jazzee\Entity\TOEFLScore')->findOneBy($parameters);
                if($score) $answer->setTOEFLScore($score);
                $cron->getEntityManager()->persist($answer);
              break;
            default:
              throw new \Jazzee\Exception("Unknown test type: {$testType} when trying to match a score");
          }
        }
      }
    }
  }
}