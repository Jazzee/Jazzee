<?php
namespace Jazzee\Page;

/**
 * The Standard Application Page
 * Unless special functionaility is required all pages are of this type
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Standard extends AbstractPage implements \Jazzee\Interfaces\QueryPage, \Jazzee\Interfaces\StatusPage, \Jazzee\Interfaces\LorPage, \Jazzee\Interfaces\SirPage
{

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
   * Standard pages query by elements finding values that match
   *
   * @param \stdClass $obj
   * @return boolean
   */
  public function testQuery(\stdClass $obj)
  {
    if (isset($obj->elements)) {
      foreach ($obj->elements as $eObj) {
        if ($element = $this->_applicationPage->getPage()->getElementByTitle($eObj->title)) {
          $element->getJazzeeElement()->setController($this->_controller);
          foreach ($this->getAnswers() as $answer) {
            if ($element->getJazzeeElement()->testQuery($answer, $eObj->query)) {
              return true;
            }
          }
        }
      }
    }

    return false;
  }

  /**
   * Record the LOR answer as a child answer
   * @param \Foundation\Form\Input $input
   * @param \Jazzee\Entity\Answer $parent
   */
  public function newLorAnswer(\Foundation\Form\Input $input, \Jazzee\Entity\Answer $parent)
  {
    if ($parent->getChildren()->count() == 0) {
      $page = $parent->getPage()->getChildren()->first();
      $child = new \Jazzee\Entity\Answer();
      $parent->addChild($child);
      $child->setPage($page);
      $child->setApplicant($parent->getApplicant());
      foreach ($page->getElements() as $element) {
        $element->getJazzeeElement()->setController($this->_controller);
        foreach ($element->getJazzeeElement()->getElementAnswers($input->get('el' . $element->getId())) as $elementAnswer) {
          $child->addElementAnswer($elementAnswer);
        }
      }
      $this->_form->applyDefaultValues();
      $this->_controller->getEntityManager()->persist($child);
      //flush here so the answerId will be correct when we view
      $this->_controller->getEntityManager()->flush();
    }
  }

  /**
   * Update the LOR
   * @param \Foundation\Form\Input $input
   * @param \Jazzee\Entity\Answer $answer
   */
  public function updateLorAnswer(\Foundation\Form\Input $input, \Jazzee\Entity\Answer $answer)
  {
    foreach ($answer->getElementAnswers() as $ea) {
      $answer->getElementAnswers()->removeElement($ea);
      $this->_controller->getEntityManager()->remove($ea);
    }
    foreach ($answer->getPage()->getElements() as $element) {
      $element->getJazzeeElement()->setController($this->_controller);
      foreach ($element->getJazzeeElement()->getElementAnswers($input->get('el' . $element->getId())) as $elementAnswer) {
        $answer->addElementAnswer($elementAnswer);
      }
    }
    $this->getForm()->applyDefaultValues();
    $this->getForm()->setAction($this->_controller->getActionPath());
    $this->_controller->getEntityManager()->persist($answer);
  }

  /**
   * Delete the LOR answer children
   * @param \Jazzee\Entity\Answer $parent
   */
  public function deleteLorAnswer(\Jazzee\Entity\Answer $answer)
  {
    $applicant = $answer->getApplicant();
    $answer->getParent()->getChildren()->removeElement($answer);
    $this->_controller->getEntityManager()->remove($answer);
    $applicant->getAnswers()->removeElement($answer);
    $applicant->markLastUpdate();
    $this->_controller->getEntityManager()->persist($applicant);
  }

  public function fillLorForm(\Jazzee\Entity\Answer $answer)
  {
    foreach ($answer->getPage()->getElements() as $element) {
      $element->getJazzeeElement()->setController($this->_controller);
      $value = $element->getJazzeeElement()->formValue($answer);
      if ($value) {
        $this->getForm()->getElementByName('el' . $element->getId())->setValue($value);
      }
    }
  }

  /**
   * Render standrad LOR pdf section
   * @param \Jazzee\ApplicantPDF $pdf
   * @param \Jazzee\Entity\Page $page
   * @param \Jazzee\Entity\Answer $answer
   */
  public function renderLorPdfAnswer(\Jazzee\ApplicantPDF $pdf, \Jazzee\Entity\Page $page, \Jazzee\Entity\Answer $answer)
  {
    $pdf->addText($page->getTitle() . "\n", 'h5');
    $this->renderPdfAnswer($pdf, $page, $answer);
  }
  
  /**
   * Get the values for each element for use in the PDF template
   * Order them by the sortElement varialbe if it is set
   * @return array
   */
  public function getPdfTemplateValues()
  {
    if ($displaySortElementId = $this->_applicationPage->getPage()->getVar('displaySortElement') and $displaySortElement = $this->_applicationPage->getPage()->getElementById($displaySortElementId)) {
      $displaySortElement->getJazzeeElement()->setController($this->_controller);
      $categories = array();
      foreach ($this->getAnswers() as $answer) {
        $categories[$displaySortElement->getJazzeeElement()->rawValue($answer)][] = $answer;
      }
      ksort($categories);
      $values = array();
      foreach($this->_applicationPage->getPage()->getElements() as $element){
        $elementValues = array();
        foreach($categories as $arr){
          foreach($arr as $answer){
            $element->getJazzeeElement()->setController($this->_controller);
            $elementValues[] = $element->getJazzeeElement()->rawValue($answer);
          }
        }
        $values[$element->getId()] = implode("\n", $elementValues);
      }
    } else {
      $values = parent::getPdfTemplateValues();
    }
    return $values;
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
      case 'displaySortElement':
        if (!empty($value)) {
          if(!$element = $this->_applicationPage->getPage()->getElementById($value)){
            throw new \Jazzee\Exception("displaySortElement must be a valid element ID for the page.  {$value} is not.");
          }
          $value = $element->getId();
        }
      break;
    }
    parent::setVar($name, $value);
  }

  public static function applyPageElement()
  {
    return 'Standard-apply_page';
  }

  public static function pageBuilderScriptPath()
  {
    return 'resource/scripts/page_types/JazzeePageStandard.js';
  }

  public static function applyStatusElement()
  {
    return 'Standard-apply_status';
  }

  public static function applicantsSingleElement()
  {
    return 'Standard-applicants_single';
  }

  public static function lorPageElement()
  {
    return 'Standard-lor_page';
  }

  public static function lorReviewElement()
  {
    return 'Standard-lor_review';
  }

  public static function lorApplicantsSingleElement()
  {
    return 'Standard-lor_applicants_single';
  }

  public static function sirPageElement()
  {
    return 'Standard-sir_page';
  }

  public static function sirApplicantsSingleElement()
  {
    return 'Standard-sir_applicants_single';
  }

}