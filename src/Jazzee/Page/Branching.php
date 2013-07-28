<?php
namespace Jazzee\Page;

/**
 * Branch a child page depending on an applicant input
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Branching extends Standard
{

  /**
   * Create the branching form
   * @return \Foundation\Form
   */
  protected function makeForm()
  {
    $form = new \Foundation\Form;
    $form->setAction($this->_controller->getActionPath());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());

    $element = $field->newElement('SelectList', 'branching');
    $element->setLabel($this->_applicationPage->getPage()->getVar('branchingElementLabel'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    foreach ($this->_applicationPage->getPage()->getChildren() as $child) {
      $element->newItem($child->getId(), $child->getTitle());
    }
    $form->newHiddenElement('level', 1);
    $form->newButton('submit', 'Next');

    return $form;
  }

  /**
   * Branching Page Form
   * Replaces the form with the correct branch
   * @param \Jazzee\Entity\Page $page
   */
  protected function branchingForm(\Jazzee\Entity\Page $page)
  {
    $form = new \Foundation\Form;
    $form->setAction($this->_controller->getActionPath());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($page->getInstructions());

    foreach ($page->getElements() as $element) {
      $element->getJazzeeElement()->setController($this->_controller);
      $element->getJazzeeElement()->addToField($field);
    }
    $form->newHiddenElement('level', 2);
    $form->newHiddenElement('branching', $page->getId());
    $form->newButton('submit', 'Save');
    $this->_form = $form;
  }

  public function validateInput($input)
  {
    $page = $this->_applicationPage->getPage()->getChildById($input['branching']);
    $this->branchingForm($page);
    if ($input['level'] == 1) {
      return false;
    }

    return parent::validateInput($input);
  }

  public function newAnswer($input)
  {
    if (is_null($this->_applicationPage->getMax()) or count($this->getAnswers()) < $this->_applicationPage->getMax()) {
      $answer = new \Jazzee\Entity\Answer();
      $answer->setPage($this->_applicationPage->getPage());
      $this->_applicant->addAnswer($answer);
      $childAnswer = new \Jazzee\Entity\Answer;
      $childAnswer->setPage($answer->getPage()->getChildById($input->get('branching')));
      $answer->addChild($childAnswer);

      foreach ($this->_applicationPage->getPage()->getChildById($input->get('branching'))->getElements() as $element) {
        foreach ($element->getJazzeeElement()->getElementAnswers($input->get('el' . $element->getId())) as $elementAnswer) {
          $childAnswer->addElementAnswer($elementAnswer);
        }
      }

      $this->_form = $this->makeForm();
      $this->_form->applyDefaultValues();
      $this->_controller->getEntityManager()->persist($answer);
      $this->_controller->getEntityManager()->persist($childAnswer);
      $this->_controller->addMessage('success', 'Answered Saved Successfully');
      //flush here so the answerId will be correct when we view
      $this->_controller->getEntityManager()->flush();
    }
  }

  public function updateAnswer($input, $answerId)
  {
    if ($answer = $this->_applicant->findAnswerById($answerId)) {
      foreach ($answer->getElementAnswers() as $ea) {
        $this->_controller->getEntityManager()->remove($ea);
        $answer->getElementAnswers()->removeElement($ea);
      }
      foreach ($answer->getChildren() as $childAnswer) {
        $this->_controller->getEntityManager()->remove($childAnswer);
        $answer->getChildren()->removeElement($childAnswer);
      }
      $childAnswer = new \Jazzee\Entity\Answer;
      $childAnswer->setPage($answer->getPage()->getChildById($input->get('branching')));
      $answer->addChild($childAnswer);
      foreach ($this->_applicationPage->getPage()->getChildById($input->get('branching'))->getElements() as $element) {
        foreach ($element->getJazzeeElement()->getElementAnswers($input->get('el' . $element->getId())) as $elementAnswer) {
          $childAnswer->addElementAnswer($elementAnswer);
        }
      }
      $this->_form = null;
      $this->_controller->getEntityManager()->persist($answer);
      $this->_controller->getEntityManager()->persist($childAnswer);
      $this->_controller->addMessage('success', 'Answer Updated Successfully');
    }
  }

  public function fill($answerId)
  {
    if ($answer = $this->_applicant->findAnswerById($answerId)) {
      $child = $answer->getChildren()->first();
      $this->branchingForm($child->getPage());
      foreach ($child->getPage()->getElements() as $element) {
        $element->getJazzeeElement()->setController($this->_controller);
        $value = $element->getJazzeeElement()->formValue($child);
        if ($value) {
          $this->getForm()->getElementByName('el' . $element->getId())->setValue($value);
        }
      }
      $this->getForm()->setAction($this->_controller->getActionPath() . "/edit/{$answerId}");
    }
  }

  public function getXmlAnswers(\DOMDocument $dom, $version)
  {
    $answers = array();
    foreach ($this->_applicant->findAnswersByPage($this->_applicationPage->getPage()) as $answer) {
      $child = $answer->getChildren()->first();
      $xmlAnswer = $this->xmlAnswer($dom, $child, $version);
      $eXml = $dom->createElement('element');
      $eXml->setAttribute('elementId', 'branching');


      $eXml->setAttribute('title', htmlentities($answer->getPage()->getVar('branchingElementLabel'), ENT_COMPAT, 'utf-8'));
      $eXml->setAttribute('type', null);
      switch ($version) {
        case 1:
          $eXml->appendChild($dom->createCDATASection(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $child->getPage()->getTitle())));
          break;
        case 2:
          $vXml = $dom->createElement('value');
          $vXml->appendChild($dom->createCDATASection(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $child->getPage()->getTitle())));
          $eXml->appendChild($vXml);
          break;
      }
      $xmlAnswer->appendChild($eXml);
      $answers[] = $xmlAnswer;
    }

    return $answers;
  }

  /**
   * Format an answer array
   * @param \array $answer
   * @param \Jazzee\Entity\Page $page
   * 
   * @return array
   */
  protected function arrayAnswer(array $answer, \Jazzee\Entity\Page $page)
  {
    $child = $answer['children'][0];
    unset($answer['children']);
    $childPage = $page->getChildById($child['page_id']);
    $elements = $child['elements'];
    $answer['elements'] = array();
    $answer['elements'][] = array(
      'id' => 'branching',
      'title' => $page->getVar('branchingElementLabel'),
      'type' => null,
      'name' => null,
      'weight' => 0,
      'values' => array(
        array('value' => $childPage->getTitle(), 'name' => null, 'id'=>null)
      )
    );
    foreach ($elements as $elementId => $elementAnswers) {
      $element = $childPage->getElementById($elementId);
      $answer['elements'][] = $element->getJazzeeElement()->formatApplicantArray($elementAnswers);
    }
    if(!is_null($answer['attachment'])){
      $answer['attachment'] = $this->arrayAnswerAttachment($answer['attachment'], $page);
    }

    return $answer;
  }

  /**
   * Branchign pages get special CSV headers so all the branches are reprsented
   * @return array
   */
  public function getCsvHeaders()
  {
    $headers = array();
    $headers[] = $this->_applicationPage->getPage()->getVar('branchingElementLabel');
    foreach ($this->_applicationPage->getPage()->getChildren() as $child) {
      foreach ($child->getElements() as $element) {
        $headers[] = $child->getTitle() . ' ' . $element->getTitle();
      }
    }

    return $headers;
  }

  /**
   * Branching pages return elements for every page
   * @param array $pageArr
   * @param int $position
   * @return array
   */
  public function getCsvAnswer(array $pageArr, $position)
  {
    $arr = array();
    if (isset($pageArr['answers']) AND array_key_exists($position, $pageArr['answers'])) {
      $arr[] = $pageArr['answers'][$position]['elements'][0]['values'][0]['value'];
    } else {
      $arr[] = '';
    }
    foreach($this->_applicationPage->getPage()->getChildren() as $child){
      foreach ($child->getElements() as $element) {
        $value = '';
        if (isset($pageArr['answers']) and array_key_exists($position, $pageArr['answers'])) {
          foreach($pageArr['answers'][$position]['elements'] as $eArr){
            if($eArr['id'] == $element->getId()){
              $value = $eArr['displayValue'];
              break;
            }
          }
        }
        $arr[] = $value;
      }
    }

    return $arr;
  }

  /**
   * Setup the default variables
   */
  public function setupNewPage()
  {
    $defaultVars = array(
      'branchingElementLabel' => ''
    );
    foreach ($defaultVars as $name => $value) {
      $var = $this->_applicationPage->getPage()->setVar($name, $value);
      $this->_controller->getEntityManager()->persist($var);
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
    if($this->getStatus() == \Jazzee\Interfaces\Page::SKIPPED){
      $pdf->addText("Applicant Skipped this page.\n", 'p');
    } else {
      foreach ($this->getAnswers() as $answer) {
        $childAnswer = $answer->getChildren()->first();
        $childPage = $childAnswer->getPage();
        $pdf->addText("{$this->_applicationPage->getPage()->getVar('branchingElementLabel')}: ", 'b');
        $pdf->addText("{$childPage->getTitle()}\n", 'p');
        $this->renderPdfAnswer($pdf, $childPage, $childAnswer);
        if ($attachment = $answer->getAttachment()) {
          $pdf->addPdf($attachment->getAttachment());
        }
        $pdf->addText("\n", 'p');
      }
    }
  }
  
  /**
   * Get the values for each element for use in the PDF template
   * @return array
   */
  public function getPdfTemplateValues()
  {
    $values = parent::getPdfTemplateValues();
    foreach($this->_applicationPage->getPage()->getChildren() as $child){
      foreach($child->getElements() as $element){
        $elementValues = array();
        foreach($this->getAnswers() as $answer){
          $childAnswer = $answer->getChildren()->first();
          $element->getJazzeeElement()->setController($this->_controller);
          $elementValues[] = $element->getJazzeeElement()->rawValue($childAnswer);
        }
        $values[$element->getId()] = implode("\n", $elementValues);
      }
      $branchingElementValues = array();
      foreach($this->getAnswers() as $answer){
        $childAnswer = $answer->getChildren()->first();
        $branchingElementValues[] = $childAnswer->getPage()->getTitle();
      }
      $values[0] = implode("\n", $branchingElementValues);
    }

    return $values;
  }

  public function formatApplicantPDFTemplateArray(array $answers)
  {
    $values = parent::formatApplicantPDFTemplateArray($answers);
    $branchingElementValues = array();
    $childrenAnswers = array();
    foreach($answers as $answer){
      if(array_key_exists(0, $answer['children'])){
        $childrenAnswers[] = $answer['children'][0];
        $branchingElementValues[] = $this->_applicationPage->getPage()->getChildById($answer['children'][0]['page_id'])->getTitle();
      }
    }
    $values[0] = implode("\n", $branchingElementValues);
    foreach($this->_applicationPage->getPage()->getChildren() as $child){
      foreach($child->getElements() as $element){
        $values[$element->getId()] = $element->getJazzeeElement()->pdfTemplateValueFromArray($childrenAnswers);
      }
    }
    

    return $values;
  }
  
  /**
   * Get the values for each element for use in the PDF template
   * @return array
   */
  public function listPdfTemplateElements()
  {
    $templateElements = array();
    $templateElements['page-'.$this->_applicationPage->getPage()->getId() . '-element-0'] = $this->_applicationPage->getTitle() . ': ' . substr($this->_applicationPage->getPage()->getVar('branchingElementLabel'), 0, 64);
    foreach($this->_applicationPage->getPage()->getChildren() as $child){
      foreach($child->getElements() as $element){
        $templateElements['page-'.$this->_applicationPage->getPage()->getId() . '-element-' . $element->getId()] = $this->_applicationPage->getTitle() . ': ' . $child->getTitle() . ': ' . substr($element->getTitle(), 0, 64);
      }
    }

    return $templateElements;
  }

  /**
   * Render branching LOR pdf section
   * @param \Jazzee\ApplicantPDF $pdf
   * @param \Jazzee\Entity\Page $page
   * @param \Jazzee\Entity\Answer $answer
   */
  public function renderLorPdfAnswer(\Jazzee\ApplicantPDF $pdf, \Jazzee\Entity\Page $page, \Jazzee\Entity\Answer $answer)
  {
    $childAnswer = $answer->getChildren()->first();
    $childPage = $childAnswer->getPage();
    $pdf->addText($page->getTitle() . "\n", 'h5');
    $pdf->addText("{$page->getVar('branchingElementLabel')}: ", 'b');
    $pdf->addText("{$childPage->getTitle()}\n", 'p');
    $this->renderPdfAnswer($pdf, $childPage, $childAnswer);
  }


  public function newLorAnswer(\Foundation\Form\Input $input, \Jazzee\Entity\Answer $parent)
  {
    if ($parent->getChildren()->count() == 0) {
      $page = $parent->getPage()->getChildren()->first();
      $child = new \Jazzee\Entity\Answer();
      $parent->addChild($child);
      $child->setPage($page);

      $branch = new \Jazzee\Entity\Answer;
      $branch->setPage($page->getChildById($input->get('branching')));
      $child->addChild($branch);

      foreach ($branch->getPage()->getElements() as $element) {
        foreach ($element->getJazzeeElement()->getElementAnswers($input->get('el' . $element->getId())) as $elementAnswer) {
          $branch->addElementAnswer($elementAnswer);
        }
      }
      $this->_form->applyDefaultValues();
      $this->_controller->getEntityManager()->persist($child);
      $this->_controller->getEntityManager()->persist($branch);
      //flush here so the answerId will be correct when we view
      $this->_controller->getEntityManager()->flush();
    }
  }

  public function updateLorAnswer(\Foundation\Form\Input $input, \Jazzee\Entity\Answer $answer)
  {
    foreach ($answer->getElementAnswers() as $ea) {
      $this->_controller->getEntityManager()->remove($ea);
      $answer->getElementAnswers()->removeElement($ea);
    }
    foreach ($answer->getChildren() as $childAnswer) {
      $this->_controller->getEntityManager()->remove($childAnswer);
      $answer->getChildren()->removeElement($childAnswer);
    }

    $branch = new \Jazzee\Entity\Answer;
    $answer->addChild($branch);
    $branch->setPage($answer->getPage()->getChildById($input->get('branching')));

    foreach ($branch->getPage()->getElements() as $element) {
      foreach ($element->getJazzeeElement()->getElementAnswers($input->get('el' . $element->getId())) as $elementAnswer) {
        $branch->addElementAnswer($elementAnswer);
      }
    }
    $this->_form = null;
    $this->_controller->getEntityManager()->persist($branch);
  }

  public function fillLorForm(\Jazzee\Entity\Answer $answer)
  {
    $child = $answer->getChildren()->first();
    $this->branchingForm($child->getPage());
    foreach ($child->getPage()->getElements() as $element) {
      $element->getJazzeeElement()->setController($this->_controller);
      $value = $element->getJazzeeElement()->formValue($child);
      if ($value) {
        $this->getForm()->getElementByName('el' . $element->getId())->setValue($value);
      }
    }
  }

  /**
   * Branching pages list the children of each branch
   * 
   * @return array
   */
  public function listDisplayElements()
  {
    $elements = parent::listDisplayElements();
    $weight = count($elements);
    $elements[] = new \Jazzee\Display\Element('page', $this->_applicationPage->getPage()->getVar('branchingElementLabel'), $weight++, 'branchingPageSelection', $this->_applicationPage->getPage()->getId());
    foreach($this->_applicationPage->getPage()->getChildren() as $child){
      foreach($child->getApplicationPageJazzeePage()->listDisplayElements() as $displayElement){
        if($displayElement->type != 'page' and !in_array($displayElement->name, array('attachment', 'answerPublicStatus', 'answerPrivateStatus'))){
          $elements[] = new \Jazzee\Display\Element($displayElement->type, $this->_applicationPage->getTitle() .' ' . $displayElement->title, $weight++, $displayElement->name, $displayElement->pageId);
        }
      }
    }
    $elements[] = new \Jazzee\Display\Element('page', 'Attacment', $weight++, 'attachment', $this->_applicationPage->getPage()->getId());

    return $elements;
  }

  public static function applyPageElement()
  {
    return 'Branching-apply_page';
  }

  public static function pageBuilderScriptPath()
  {
    return 'resource/scripts/page_types/JazzeePageBranching.js';
  }

  public static function applicantsSingleElement()
  {
    return 'Branching-applicants_single';
  }

  public static function lorPageElement()
  {
    return 'Branching-lor_page';
  }

  public static function lorApplicantsSingleElement()
  {
    return 'Branching-lor_applicants_single';
  }

  public static function lorReviewElement()
  {
    return 'Branching-lor_review';
  }

  public static function sirApplicantsSingleElement()
  {
    return 'Branching-sir_applicants_single';
  }

}