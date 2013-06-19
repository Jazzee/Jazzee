<?php
namespace Jazzee\Page;

/**
 * The ETSMatch Application Page
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ETSMatch extends AbstractPage implements \Jazzee\Interfaces\StatusPage
{
  /**
   * These fixedIDs make it easy to find the element we are looking for
   * @const integer
   */

  const FID_TEST_TYPE = 2;
  const FID_REGISTRATION_NUMBER = 4;
  const FID_TEST_DATE = 6;

  /**
   * Skip an optional page
   *
   */
  public function do_skip()
  {
    if (count($this->getAnswers())) {
      $this->_controller->addMessage('error', 'You must delete your existing answers before you can skip this page.');

      return false;
    }
    if (!$this->_applicationPage->isRequired()) {
      $answer = new \Jazzee\Entity\Answer();
      $answer->setPage($this->_applicationPage->getPage());
      $this->_applicant->addAnswer($answer);
      $answer->setPageStatus(self::SKIPPED);
      $this->_controller->getEntityManager()->persist($answer);
    }
  }

  public function do_unskip()
  {
    $answers = $this->getAnswers();
    if (count($answers) and $answers[0]->getPageStatus() == self::SKIPPED) {
      $this->_applicant->getAnswers()->removeElement($answers[0]);
      $this->_controller->getEntityManager()->remove($answers[0]);
    }
  }

  public function getStatus()
  {
    $answers = $this->getAnswers();
    if (!$this->_applicationPage->isRequired() and count($answers) and $answers[0]->getPageStatus() == self::SKIPPED) {
      return self::SKIPPED;
    }
    if (is_null($this->_applicationPage->getMin()) and count($answers)) {
      return self::COMPLETE;
    }
    if (!is_null($this->_applicationPage->getMin()) and count($answers) >= $this->_applicationPage->getMin()) {
      return self::COMPLETE;
    }

    return self::INCOMPLETE;
  }

  public function getArrayStatus(array $answers)
  {
    if (!$this->_applicationPage->isRequired() and count($answers) and $answers[0]['pageStatus'] == self::SKIPPED) {
      return self::SKIPPED;
    }
    if (is_null($this->_applicationPage->getMin()) and count($answers)) {
      return self::COMPLETE;
    }
    if (!is_null($this->_applicationPage->getMin()) and count($answers) >= $this->_applicationPage->getMin()) {
      return self::COMPLETE;
    }

    return self::INCOMPLETE;
  }

  /**
   * Try and match a score to an answer
   *
   * @param \Jazzee\Entity\Answer
   */
  public function matchScore(\Jazzee\Entity\Answer $answer)
  {
    if ($answer->getPageStatus() == self::SKIPPED) {
      return;
    }
    if (!is_null($answer->getGREScore()) and !is_null($answer->getTOEFLScore())) {
      return; //we already have a match
    }
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
    switch ($testType) {
      case 'GRE/GRE Subject':
        $score = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\GREScore')->findOneBy($parameters);
        if ($score) {
          $answer->setGreScore($score);
        }
          break;
      case 'TOEFL':
        $score = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\TOEFLScore')->findOneBy($parameters);
        if ($score) {
          $answer->setTOEFLScore($score);
        }
          break;
      default:
        throw new \Jazzee\Exception("Unknown test type: {$testType} when trying to match a score");
    }
  }

  /**
   * Fix the registration number
   *
   * Transform out leading 0s and non numeric chars before processing input
   * This way if we remove everything there will still be ab error for required input being blank
   * @param array $arr
   * @return \Foundation\Form\Input or boolean
   */
  public function validateInput($arr)
  {
    $element = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_REGISTRATION_NUMBER);
    $key = 'el' . $element->getId();
    $arr[$key] = preg_replace('#[^0-9]#', '', $arr[$key]);
    $arr[$key] = ltrim($arr[$key], '0');

    return parent::validateInput($arr);
  }

  public function newAnswer($input)
  {
    parent::newAnswer($input);
    //attempt to match any scores
    foreach ($this->getAnswers() as $answer) {
      $this->matchScore($answer);
    }
  }

  public function updateAnswer($input, $answerId)
  {
    parent::updateAnswer($input, $answerId);
    //attempt to match any scores
    foreach ($this->getAnswers() as $answer) {
      $this->matchScore($answer);
    }
  }

  /**
   * Find Possible gre score matches
   * @param integer $answerID
   * @param array $postData
   */
  public function do_findPossibleScores($postData)
  {
    $this->checkIsAdmin();
    $form = new \Foundation\Form;
    $field = $form->newField();
    $field->setLegend('Find Matching Scores for ' . $this->_applicant->getFullName());
    
    $existingScores = array();
    foreach($this->getAnswers() as $answer){
      $date = strtotime($this->_applicationPage->getPage()->getElementByFixedId(self::FID_TEST_DATE)->getJazzeeElement()->displayValue($answer));
      $uniqueId = 
        $this->_applicationPage->getPage()->getElementByFixedId(self::FID_REGISTRATION_NUMBER)->getJazzeeElement()->displayValue($answer) .
        $this->_applicationPage->getPage()->getElementByFixedId(self::FID_TEST_TYPE)->getJazzeeElement()->displayValue($answer) .
        date('m', $date) . date('Y', $date); 
      $existingScores[$uniqueId] = $answer;
        
    }
    
    $element = $field->newElement('CheckboxList', 'greMatches');
    $element->setLabel('Possible GRE');
    
    foreach ($this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\GREScore')->findByName(substr($this->_applicant->getFirstName(), 0, 2) . '%', substr($this->_applicant->getLastName(), 0, 3) . '%') as $score) {
      $uniqueId = $score->getRegistrationNumber() . 'GRE/GRE Subject' . $score->getTestDate()->format('m') . $score->getTestDate()->format('Y');
      if(!array_key_exists($uniqueId, $existingScores)){
        $element->newItem($score->getId(), $score->getLastName() . ',  ' . $score->getFirstName() . ' ' . $score->getMiddleInitial() . ' ' . $score->getTestDate()->format('m/d/Y'));
      } else {
        if(!$existingScores[$uniqueId]->getGREScore()) {
          $element->addMessage('The system found at least one match for a GRE score the applicant had previously entered.  You may need to refresh this page to view that match.');
          $this->matchScore($existingScores[$uniqueId]);
        }
      }
    }

    $element = $field->newElement('CheckboxList', 'toeflMatches');
    $element->setLabel('Possible TOEFL');
    foreach ($this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\TOEFLScore')->findByName(substr($this->_applicant->getFirstName(), 0, 2) . '%', substr($this->_applicant->getLastName(), 0, 3) . '%') as $score) {
      $uniqueId = $score->getRegistrationNumber() . 'TOEFL' . $score->getTestDate()->format('m') . $score->getTestDate()->format('Y');
      if(!array_key_exists($uniqueId, $existingScores)){
        $element->newItem($score->getId(), $score->getLastName() . ',  ' . $score->getFirstName() . ' ' . $score->getMiddleName() . ' ' . $score->getTestDate()->format('m/d/Y'));
      } else {
        if(!$existingScores[$uniqueId]->getTOEFLScore()) {
          $element->addMessage('The system found at least one match for a TOEFL score the applicant had previously entered.  You may need to refresh this page to view that match.');
          $this->matchScore($existingScores[$uniqueId]);
        }
      }
    }
    $form->newButton('submit', 'Match Scores');
    if (!empty($postData)) {
      //create a blank for so it can get values from parent::newAnswer
      $this->_form = new \Foundation\Form();
      if ($input = $form->processInput($postData)) {
        if ($input->get('greMatches')) {
          foreach ($input->get('greMatches') as $scoreId) {
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
        if ($input->get('toeflMatches')) {
          foreach ($input->get('toeflMatches') as $scoreId) {
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
   * Find Possible gre score matches
   * @param integer $answerID
   * @param array $postData
   */
  public function do_searchScores($postData)
  {
    $this->checkIsAdmin();
    $searchForm = new \Foundation\Form;
    $field = $searchForm->newField();
    $field->setLegend('Search Scores');
    
    $element = $field->newElement('TextInput', 'firstName');
    $element->setLabel('First Name');
    $element->setValue($this->_applicant->getFirstName());
    $element = $field->newElement('TextInput', 'lastName');
    $element->setLabel('Last Name');
    $element->setValue($this->_applicant->getLastName());
    $searchForm->newHiddenElement('level', 'search');
    $searchForm->newButton('submit', 'Search Scores');
    
    $matchForm = new \Foundation\Form;
    $field = $matchForm->newField();
    $field->setLegend('Select scores to match');
    
    $greElement = $field->newElement('CheckboxList', 'greMatches');
    $greElement->setLabel('Possible GRE');
    
    $toeflElement = $field->newElement('CheckboxList', 'toeflMatches');
    $toeflElement->setLabel('Possible TOEFL');
    
    $matchForm->newHiddenElement('level', 'match');
    $matchForm->newButton('submit', 'Match Scores');
    
    $form = $searchForm;
    if (!empty($postData)) {
      if($postData['level'] == 'search'){
        if($input = $searchForm->processInput($postData)){
          $matchForm->newHiddenElement('firstName', $input->get('firstName'));
          $matchForm->newHiddenElement('lastName', $input->get('lastName'));
          
          $existingScores = array();
          foreach($this->getAnswers() as $answer){
            $date = strtotime($this->_applicationPage->getPage()->getElementByFixedId(self::FID_TEST_DATE)->getJazzeeElement()->displayValue($answer));
            $uniqueId = 
              $this->_applicationPage->getPage()->getElementByFixedId(self::FID_REGISTRATION_NUMBER)->getJazzeeElement()->displayValue($answer) .
              $this->_applicationPage->getPage()->getElementByFixedId(self::FID_TEST_TYPE)->getJazzeeElement()->displayValue($answer) .
              date('m', $date) . date('Y', $date); 
            $existingScores[$uniqueId] = $answer;
          }
          foreach ($this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\GREScore')->findByName($input->get('firstName') . '%', $input->get('lastName') . '%') as $score) {
            $uniqueId = $score->getRegistrationNumber() . 'GRE/GRE Subject' . $score->getTestDate()->format('m') . $score->getTestDate()->format('Y');
            if(!array_key_exists($uniqueId, $existingScores)){
              $greElement->newItem($score->getId(), $score->getLastName() . ',  ' . $score->getFirstName() . ' ' . $score->getMiddleInitial() . ' ' . $score->getTestDate()->format('m/d/Y'));
            } else {
              if(!$existingScores[$uniqueId]->getGREScore()) {
                $greElement->addMessage('The system found at least one match for a GRE score the applicant had previously entered.  You may need to refresh this page to view that match.');
                $this->matchScore($existingScores[$uniqueId]);
              }
            }
          }


          foreach ($this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\TOEFLScore')->findByName($input->get('firstName') . '%', $input->get('lastName') . '%') as $score) {
            $uniqueId = $score->getRegistrationNumber() . 'TOEFL' . $score->getTestDate()->format('m') . $score->getTestDate()->format('Y');
            if(!array_key_exists($uniqueId, $existingScores)){
              $toeflElement->newItem($score->getId(), $score->getLastName() . ',  ' . $score->getFirstName() . ' ' . $score->getMiddleName() . ' ' . $score->getTestDate()->format('m/d/Y'));
            } else {
              if(!$existingScores[$uniqueId]->getTOEFLScore()) {
                $toeflElement->addMessage('The system found at least one match for a TOEFL score the applicant had previously entered.  You may need to refresh this page to view that match.');
                $this->matchScore($existingScores[$uniqueId]);
              }
            }
          }
          $form = $matchForm;
        }
      } else if($postData['level'] == 'match'){
        $form = $matchForm;
        
        //Re add all the matches to the elements so they will validate
        foreach ($this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\GREScore')->findByName($postData['firstName'] . '%', $postData['lastName'] . '%') as $score) {
          $greElement->newItem($score->getId(), $score->getLastName() . ',  ' . $score->getFirstName() . ' ' . $score->getMiddleInitial() . ' ' . $score->getTestDate()->format('m/d/Y'));
        }
        foreach ($this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\TOEFLScore')->findByName($postData['firstName'] . '%', $postData['lastName'] . '%') as $score) {
          $toeflElement->newItem($score->getId(), $score->getLastName() . ',  ' . $score->getFirstName() . ' ' . $score->getMiddleName() . ' ' . $score->getTestDate()->format('m/d/Y'));
        }

        if($input = $matchForm->processInput($postData)){
          //create a blank for so it can get values from parent::newAnswer
          $this->_form = new \Foundation\Form();
          if ($input->get('greMatches')) {
            foreach ($input->get('greMatches') as $scoreId) {
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
          if ($input->get('toeflMatches')) {
            foreach ($input->get('toeflMatches') as $scoreId) {
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
        }
      }
    }

    return $form;
  }

  /**
   * Create the ets match form
   */
  public function setupNewPage()
  {
    $entityManager = $this->_controller->getEntityManager();
    $types = $entityManager->getRepository('\Jazzee\Entity\ElementType')->findAll();
    $elementTypes = array();
    foreach ($types as $type) {
      $elementTypes[$type->getClass()] = $type;
    };

    $element = new \Jazzee\Entity\Element;
    $this->_applicationPage->getPage()->addElement($element);
    $element->setType($elementTypes['\\Jazzee\Element\RadioList']);
    $element->setTitle('Test Type');
    $element->required();
    $element->setWeight(1);
    $element->setFixedId(self::FID_TEST_TYPE);
    $entityManager->persist($element);

    $item = new \Jazzee\Entity\ElementListItem;
    $element->addItem($item);
    $item->setValue('GRE/GRE Subject');
    $item->setWeight(1);
    $entityManager->persist($item);

    $item = new \Jazzee\Entity\ElementListItem;
    $element->addItem($item);
    $item->setValue('TOEFL');
    $item->setWeight(2);
    $entityManager->persist($item);

    $element = new \Jazzee\Entity\Element;
    $this->_applicationPage->getPage()->addElement($element);
    $element->setType($elementTypes['\\Jazzee\Element\TextInput']);
    $element->setTitle('ETS Registration Number');
    $element->setFormat('no leading zeros or hyphens');
    $element->required();
    $element->setWeight(2);
    $element->setFixedId(self::FID_REGISTRATION_NUMBER);
    $entityManager->persist($element);

    $element = new \Jazzee\Entity\Element;
    $this->_applicationPage->getPage()->addElement($element);
    $element->setType($elementTypes['\\Jazzee\Element\ShortDate']);
    $element->setTitle('Test Date');
    $element->required();
    $element->setWeight(3);
    $element->setFixedId(self::FID_TEST_DATE);
    $entityManager->persist($element);
  }

  /**
   * Add Scores to the answer
   * @see jazzee/src/Jazzee/Entity/Page/Jazzee\Entity\Page.AbstractPage::xmlAnswer()
   */
  protected function xmlAnswer(\DomDocument $dom, \Jazzee\Entity\Answer $answer, $version)
  {
    $xml = parent::xmlAnswer($dom, $answer, $version);
    if ($answer->getMatchedScore()) {
      $scoreXml = $dom->createElement('score');
      foreach ($answer->getMatchedScore()->getSummary() as $name => $value) {
        $element = $dom->createElement('component');
        $element->setAttribute('name', htmlentities($name, ENT_COMPAT, 'utf-8'));
        $element->appendChild($dom->createCDATASection($value));
        $scoreXml->appendChild($element);
      }
      $xml->appendChild($scoreXml);
    }

    return $xml;
  }

  /**
   * Create a table from answers
   * and append any attached PDFs
   * @param \Jazzee\ApplicantPDF $pdf
   */
  public function renderPdfSection(\Jazzee\ApplicantPDF $pdf)
  {
    $pdf->addText($this->_applicationPage->getTitle() . "\n", 'h3');
    if($this->getStatus() == \Jazzee\Interfaces\Page::SKIPPED){
      $pdf->addText("Applicant Skipped this page.\n", 'p');
    } else {
      foreach ($this->getAnswers() as $answer) {
        $this->renderPdfAnswer($pdf, $this->_applicationPage->getPage(), $answer);
        if ($answer->getMatchedScore()) {
          foreach ($answer->getMatchedScore()->getSummary() as $key => $value) {
            $pdf->addText("{$key}: ", 'b');
            $pdf->addText("{$value}\n", 'p');
          }
        } else {
          $pdf->addText('This score has not been received from ETS.', 'p');
        }
        $pdf->addText("\n", 'p');
      }
    }
  }

  /**
   * Match unmatched scores as a cron task
   * @param AdminCronController $cron
   */
  public static function runCron(\AdminCronController $cron)
  {
    $pageType = $cron->getEntityManager()->getRepository('\Jazzee\Entity\PageType')->findOneBy(array('class' => '\Jazzee\Page\ETSMatch'));
    $allETSMatchPages = $cron->getEntityManager()->getRepository('\Jazzee\Entity\Page')->findBy(array('type' => $pageType->getId()));
    $count = array(
        '\Jazzee\Entity\GREScore' => 0,
        '\Jazzee\Entity\TOEFLScore' => 0
    );
    $scores = array(
      '\Jazzee\Entity\GREScore' => $cron->getEntityManager()->getRepository('\Jazzee\Entity\GREScore')->findAllArray(),
      '\Jazzee\Entity\TOEFLScore' => $cron->getEntityManager()->getRepository('\Jazzee\Entity\TOEFLScore')->findAllArray()
    );
    
    foreach ($allETSMatchPages as $page) {
      //get all the answers without a matching score.
      $answers = $cron->getEntityManager()->getRepository('\Jazzee\Entity\Answer')->findUnmatchedScores($page);
      $elements = array();
      $elements['testType'] = $page->getElementByFixedId(self::FID_TEST_TYPE);
      $elements['registrationNumber'] = $page->getElementByFixedId(self::FID_REGISTRATION_NUMBER);
      $elements['testDate'] = $page->getElementByFixedId(self::FID_TEST_DATE);
      $unmatchedScores = array(
        '\Jazzee\Entity\GREScore' => array(),
        '\Jazzee\Entity\TOEFLScore' => array()
      );
      foreach ($answers as $arr) {
        $answerElements = array();
        foreach($arr['elements'] as $eArr){
          $answerElements[$eArr['element_id']] = array($eArr);
        }
        $value = $elements['testType']->getJazzeeElement()->formatApplicantArray($answerElements[$elements['testType']->getId()]);
        $value = $value['values'][0]['value'];
        if($value == 'GRE/GRE Subject'){
          $testType = '\Jazzee\Entity\GREScore';
        } else if ($value == 'TOEFL'){
          $testType = '\Jazzee\Entity\TOEFLScore';
        } else {
          throw new \Jazzee\Exception("Unknown test type: {$value} when trying to match a score");
        }
        
        $value = $elements['registrationNumber']->getJazzeeElement()->formatApplicantArray($answerElements[$elements['registrationNumber']->getId()]);
        $registrationNumber = $value['values'][0]['value'];
        
        $value = $elements['testDate']->getJazzeeElement()->formatApplicantArray($answerElements[$elements['testDate']->getId()]);
        $testDate = $value['values'][0]['value'];
        $testMonth = date('m', strtotime($testDate));
        $testYear = date('Y', strtotime($testDate));
        $unmatchedScores[$testType][$registrationNumber . $testMonth . $testYear] = $arr['id'];
      }
      foreach($unmatchedScores as $scoreEntityType => $arr){
        foreach($arr as $uniqueId => $answerId){
          if(array_key_exists($uniqueId, $scores[$scoreEntityType])){
            $count[$scoreEntityType] += $cron->getEntityManager()->getRepository($scoreEntityType)->matchScore($answerId, $scores[$scoreEntityType][$uniqueId]);
          }
        }
      }
    }
    if ($count['\Jazzee\Entity\GREScore']) {
      $cron->log("Found {$count['\Jazzee\Entity\GREScore']} new GRE score matches");
    }
    if ($count['\Jazzee\Entity\TOEFLScore']) {
      $cron->log("Found {$count['\Jazzee\Entity\TOEFLScore']} new TOEFL score matches");
    }
  }

  /**
   * ETS Pages include all the score information
   * 
   * @return array
   */
  public function listDisplayElements()
  {
    $elements = parent::listDisplayElements();
    $items = array(
      'greRegistrationNumber' => 'GRE Registration Number',
      'greDepartmentName' => 'GRE Department Name',
      'greTestDate' => 'GRE Test Date',
      'greTestName' => 'GRE Test Name',
      'greScore1' => 'GRE Score 1',
      'greScore2' => 'GRE Score 2',
      'greScore3' => 'GRE Score 3',
      'greScore4' => 'GRE Score 4',
      'toeflRegistrationNumber' => 'TOEFL Registration Number',
      'toeflNativeCountry' => 'TOEFL Native Country',
      'toeflNativeLanguage' => 'TOEFL Native Language',
      'toeflTestDate' => 'TOEFL Test Date',
      'toeflTestType' => 'TOEFL Test Type',
      'toeflListening' => 'TOEFL Listening',
      'toeflWriting' => 'TOEFL Writing',
      'toeflReading' => 'TOEFL Reading',
      'toeflEssay' => 'TOEFL Essay',
      'toeflTotal' => 'TOEFL Total'
    );
    $weight = count($elements);
    foreach($items as $name => $title){
      $elements[] = new \Jazzee\Display\Element('page', $title, $weight++, $name, $this->_applicationPage->getPage()->getId());
    }

    return $elements;
  }

  public static function applyPageElement()
  {
    return 'ETSMatch-apply_page';
  }

  public static function pageBuilderScriptPath()
  {
    return 'resource/scripts/page_types/JazzeePageETSMatch.js';
  }

  public static function applyStatusElement()
  {
    return 'ETSMatch-apply_status';
  }

  public static function applicantsSingleElement()
  {
    return 'ETSMatch-applicants_single';
  }

}