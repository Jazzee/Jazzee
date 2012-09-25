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
  public function do_findScores($postData)
  {
    $this->checkIsAdmin();
    $form = new \Foundation\Form;
    $field = $form->newField();
    $field->setLegend('Find Matching Scores');
    $element = $field->newElement('CheckboxList', 'greMatches');
    $element->setLabel('Possible GRE');
    foreach ($this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\GREScore')->findByName(substr($this->_applicant->getFirstName(), 0, 1) . '%', substr($this->_applicant->getLastName(), 0, 2) . '%') as $score) {
      $element->newItem($score->getId(), $score->getLastName() . ',  ' . $score->getFirstName() . ' ' . $score->getMiddleInitial() . ' ' . $score->getTestDate()->format('m/d/Y'));
    }

    $element = $field->newElement('CheckboxList', 'toeflMatches');
    $element->setLabel('Possible TOEFL');
    foreach ($this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\TOEFLScore')->findByName(substr($this->_applicant->getFirstName(), 0, 1) . '%', substr($this->_applicant->getLastName(), 0, 2) . '%') as $score) {
      $element->newItem($score->getId(), $score->getLastName() . ',  ' . $score->getFirstName() . ' ' . $score->getMiddleName() . ' ' . $score->getTestDate()->format('m/d/Y'));
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
   * Create the ets match form
   * @param Entity\Page $page
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
        if ($attachment = $answer->getAttachment()) {
          $pdf->addPdf($attachment->getAttachment());
        }
        $pdf->addText("\n", 'p');
      }
      $pdf->write();
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
    $countGre = 0;
    $countToefl = 0;
    foreach ($allETSMatchPages as $page) {
      //get all the answers without a matching score.
      $answers = $cron->getEntityManager()->getRepository('\Jazzee\Entity\Answer')->findBy(array('pageStatus' => null, 'page' => $page->getId(), 'greScore' => null, 'toeflScore' => null), array('updatedAt' => 'desc'));
      foreach ($answers as $answer) {
        if (is_null($answer->getGREScore()) and is_null($answer->getTOEFLScore())) {
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
          switch ($testType) {
            case 'GRE/GRE Subject':
              $score = $cron->getEntityManager()->getRepository('\Jazzee\Entity\GREScore')->findOneBy($parameters);
              if ($score) {
                $countGre++;
                $answer->setGreScore($score);
                $cron->getEntityManager()->persist($answer);
              }
                break;
            case 'TOEFL':
              $score = $cron->getEntityManager()->getRepository('\Jazzee\Entity\TOEFLScore')->findOneBy($parameters);
              if ($score) {
                $countToefl++;
                $answer->setTOEFLScore($score);
                $cron->getEntityManager()->persist($answer);
              }
                break;
            default:
              throw new \Jazzee\Exception("Unknown test type: {$testType} when trying to match a score");
          }
        }
      }
    }
    if ($countGre) {
      $cron->log("Found {$countGre} new GRE score matches");
    }
    if ($countToefl) {
      $cron->log("Found {$countToefl} new TOEFL score matches");
    }
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