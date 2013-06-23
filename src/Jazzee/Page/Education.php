<?php
namespace Jazzee\Page;

/**
 * The education page has two branches - one if the school is on the list
 * and another if it is not.  Then non-matched school can be matched by administrators 
 * if necessary
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Education extends Standard
{
  
  /**
   * Fixed element for the school
   */
  const ELEMENT_FID_SCHOOL = 2;
  const PAGE_FID_KNOWNSCHOOL = 2;
  const PAGE_FID_NEWSCHOOL = 4;

  /**
   * Initial form is for school select, then 
   */
  protected function makeForm()
  {
    $schoolList = $this->_applicationPage->getPage()->getElementByFixedId(self::ELEMENT_FID_SCHOOL);
    $form = new \Foundation\Form;
    $form->setAction($this->_controller->getActionPath());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());
    $element = $field->newElement('TextInput', 'el' . $schoolList->getId());
    $element->setLabel('Search for School');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $form->newButton('submit', 'Search');
    
    $form->newHiddenElement('level', 1);
    $form->getElementByName('submit')->setValue('Search');
    return $form;
  }

  /**
   * Branching Page Form
   * Replaces the form with the correct branch
   * @param \Jazzee\Entity\Page $page
   * @param int $schoolId
   */
  protected function branchingForm(\Jazzee\Entity\Page $page, $schoolId)
  {
    $form = new \Foundation\Form;
    $form->setAction($this->_controller->getActionPath());
    $field = $form->newField();
    $field->setLegend($page->getTitle());
    $field->setInstructions($page->getInstructions());

    foreach ($page->getElements() as $element) {
      $element->getJazzeeElement()->setController($this->_controller);
      $element->getJazzeeElement()->addToField($field);
    }
    $form->newHiddenElement('level', 3);
    $schoolList = $this->_applicationPage->getPage()->getElementByFixedId(self::ELEMENT_FID_SCHOOL);
    $form->newHiddenElement( 'el'.$schoolList->getId(), $schoolId);
    $form->newButton('submit', 'Save');
    $this->_form = $form;
  }

  /**
   * Create a form to choose a school
   * @param array $choices
   */
  protected function pickSchoolForm($choices)
  {
    $schoolList = $this->_applicationPage->getPage()->getElementByFixedId(self::ELEMENT_FID_SCHOOL);
    $form = new \Foundation\Form;
    $form->setAction($this->_controller->getActionPath());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());

    $element = $field->newElement('RadioList', 'el'.$schoolList->getId());
    $element->setLabel('Choose School');
    $element->newItem(null, 'Enter a new School');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    asort($choices);
    foreach ($choices as $id => $value) {
      $element->newItem($id, $value);
    }
    $form->newHiddenElement('level', 2);
    $form->newButton('submit', 'Next');
    $this->_form = $form;
  }

  public function validateInput($input)
  {
    $schoolList = $this->_applicationPage->getPage()->getElementByFixedId(self::ELEMENT_FID_SCHOOL);
    if($input['level'] == 1){
      if ($input = $this->getForm()->processInput($input)) {
        $choices = array();
        $searchTerms = explode(' ',$input->get('el'.$schoolList->getId()));
        $items = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\ElementListItem')->search($schoolList, $searchTerms, array('searchTerms'));
        foreach($items as $item){
          $choices[$item->getId()] = $item->getValue();
        }
        if(count($choices) > 50){
          $this->_controller->addMessage('error', 'Your search returned too many results, please try again with more detail.');
          return false;
        }
        $this->pickSchoolForm($choices);
        return false;
      } else {
        $this->_controller->addMessage('error', self::ERROR_MESSAGE);
        return false;
      }
    } else if($input['level'] == 2 OR $input['level'] == 3){
      if(!empty($input[ 'el'.$schoolList->getId()])){
        $schoolId = $input[ 'el'.$schoolList->getId()];
        $this->_controller->setVar('schoolName', $schoolList->getItemById($schoolId)->getValue());
        $page = $this->_applicationPage->getPage()->getChildByFixedId(self::PAGE_FID_KNOWNSCHOOL);
      } else {
        $schoolId = null;
        $page = $this->_applicationPage->getPage()->getChildByFixedId(self::PAGE_FID_NEWSCHOOL);
      }
      $this->branchingForm($page, $schoolId);
      if($input['level'] == 2){
        return false;
      }
      return parent::validateInput($input);
    }
  }

  public function newAnswer($input)
  {
    if (is_null($this->_applicationPage->getMax()) or count($this->getAnswers()) < $this->_applicationPage->getMax()) {
      $answer = new \Jazzee\Entity\Answer();
      $answer->setPage($this->_applicationPage->getPage());
      foreach ($this->_applicationPage->getPage()->getElements() as $element) {
        foreach ($element->getJazzeeElement()->getElementAnswers($input->get('el' . $element->getId())) as $elementAnswer) {
          $answer->addElementAnswer($elementAnswer);
        }
      }
      $this->_applicant->addAnswer($answer);
      $childAnswer = new \Jazzee\Entity\Answer;
      $schoolList = $this->_applicationPage->getPage()->getElementByFixedId(self::ELEMENT_FID_SCHOOL);
      if($input->get('el'.$schoolList->getId()) != null){
        $schoolPage = $this->_applicationPage->getPage()->getChildByFixedId(self::PAGE_FID_KNOWNSCHOOL);
      } else {
        $schoolPage = $this->_applicationPage->getPage()->getChildByFixedId(self::PAGE_FID_NEWSCHOOL);
      }
      $childAnswer->setPage($schoolPage);
      $answer->addChild($childAnswer);

      foreach ($schoolPage->getElements() as $element) {
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
      foreach ($this->_applicationPage->getPage()->getElements() as $element) {
        foreach ($element->getJazzeeElement()->getElementAnswers($input->get('el' . $element->getId())) as $elementAnswer) {
          $answer->addElementAnswer($elementAnswer);
        }
      }
      $childAnswer = new \Jazzee\Entity\Answer;
      $element = $this->_applicationPage->getPage()->getElementByFixedId(self::ELEMENT_FID_SCHOOL);
      if($input->get('el' . $element->getId()) != null){
        $schoolPage = $this->_applicationPage->getPage()->getChildByFixedId(self::PAGE_FID_KNOWNSCHOOL);
      } else {
        $schoolPage = $this->_applicationPage->getPage()->getChildByFixedId(self::PAGE_FID_NEWSCHOOL);
      }
      $childAnswer->setPage($schoolPage);
      $answer->addChild($childAnswer);

      foreach ($schoolPage->getElements() as $element) {
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
      $element = $this->_applicationPage->getPage()->getElementByFixedId(self::ELEMENT_FID_SCHOOL);
      $schoolName = $element->getJazzeeElement()->rawValue($answer);
      if($schoolName != null){
        $this->_controller->setVar('schoolName', $schoolName);
        $schoolId = $element->getItemByValue($schoolName)->getId();
      } else {
        $schoolId = null;
      }
      $child = $answer->getChildren()->first();
      $this->branchingForm($child->getPage(), $schoolId);
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

  /**
   * Create the school choice form
   */
  public function setupNewPage()
  {
    $entityManager = $this->_controller->getEntityManager();
    $type = $entityManager->getRepository('\Jazzee\Entity\ElementType')->findOneBy(array('class'=>'\\Jazzee\Element\SelectList'));
    $standardPageType = $entityManager->getRepository('\Jazzee\Entity\PageType')->findOneBy(array('class'=>'\\Jazzee\Page\Standard'));

    $element = new \Jazzee\Entity\Element;
    $this->_applicationPage->getPage()->addElement($element);
    $element->setType($type);
    $element->setTitle('School Name');
    $element->required();
    $element->setWeight(1);
    $element->setFixedId(self::ELEMENT_FID_SCHOOL);
    $entityManager->persist($element);
    
    $knownSchool = new \Jazzee\Entity\Page();
    $knownSchool->setType($standardPageType);
    $knownSchool->setFixedId(self::PAGE_FID_KNOWNSCHOOL);
    $knownSchool->setTitle('Known School');
    $this->_applicationPage->getPage()->addChild($knownSchool);
    $entityManager->persist($knownSchool);
    
    $newSchool = new \Jazzee\Entity\Page();
    $newSchool->setType($standardPageType);
    $newSchool->setFixedId(self::PAGE_FID_NEWSCHOOL);
    $newSchool->setTitle('New School');
    $this->_applicationPage->getPage()->addChild($newSchool);
    $entityManager->persist($newSchool);
    
  }

  /**
   * XML answers include the type of school page (known or unknown)
   * as well as the school name if it is available
   * @param \DOMDocument $dom
   * @param type $version
   * @return type
   */
  public function getXmlAnswers(\DOMDocument $dom, $version)
  {
    $answers = array();
    foreach ($this->_applicant->findAnswersByPage($this->_applicationPage->getPage()) as $answer) {
      $child = $answer->getChildren()->first();
      $xmlAnswer = $this->xmlAnswer($dom, $child, $version);
      $eXml = $dom->createElement('element');
      $eXml->setAttribute('elementId', 'type');


      $eXml->setAttribute('title', htmlentities('Type', ENT_COMPAT, 'utf-8'));
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
      $xmlAnswer->appendChild($answer->getPage()->getElementByFixedId(\Jazzee\Page\Education::ELEMENT_FID_SCHOOL)->getJazzeeElement()->getXmlAnswer($dom, $answer, $version));
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
    $elements = $answer['elements'];
    $answer['elements'] = array();
    foreach ($elements as $elementId => $elementAnswers) {
      $element = $page->getElementById($elementId);
      $answer['elements'][] = $element->getJazzeeElement()->formatApplicantArray($elementAnswers);
    }

    $child = $answer['children'][0];
    $childPage = $page->getChildById($child['page_id']);
    $answer['elements'][] = array(
      'id' => 'type',
      'title' => 'Type',
      'type' => null,
      'name' => null,
      'weight' => 0,
      'values' => array(
        array('value' => $childPage->getTitle(), 'name' => null, 'id'=>null)
      ),
      'displayValue' => $childPage->getTitle()
    );
    
    $childElements = $child['elements'];
    unset($answer['children']);
    
    
    foreach ($childElements as $elementId => $elementAnswers) {
      $element = $childPage->getElementById($elementId);
      $answer['elements'][] = $element->getJazzeeElement()->formatApplicantArray($elementAnswers);
    }
    if(!is_null($answer['attachment'])){
      $answer['attachment'] = $this->arrayAnswerAttachment($answer['attachment'], $page);
    }

    return $answer;
  }

  /**
   * Represent all branches and the type/school name as well
   * @return array
   */
  public function getCsvHeaders()
  {
    $headers = parent::getCsvHeaders();
    $headers[] = 'Type';
    foreach ($this->_applicationPage->getPage()->getChildren() as $child) {
      foreach ($child->getElements() as $element) {
        $headers[] = $child->getTitle() . ' ' . $element->getTitle();
      }
    }

    return $headers;
  }

   /**
   * CSV elements for main page and any branches as well
   * @param array $pageArr
   * @param int $position
   * @return array
   */
  public function getCsvAnswer(array $pageArr, $position)
  {
    $arr = array();
    foreach ($this->_applicationPage->getPage()->getElements() as $element) {
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
    if (isset($pageArr['answers']) and array_key_exists($position, $pageArr['answers'])) {
      $value = '';
      if (isset($pageArr['answers']) and array_key_exists($position, $pageArr['answers'])) {
        foreach($pageArr['answers'][$position]['elements'] as $eArr){
          if($eArr['id'] == 'type'){
            $value = $eArr['displayValue'];
            break;
          }
        }
      }
      $arr[] = $value;
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
        $pdf->addText("Type: ", 'b');
        $pdf->addText("{$childPage->getTitle()}\n", 'p');
        $this->renderPdfAnswer($pdf, $this->_applicationPage->getPage(), $answer);
        $this->renderPdfAnswer($pdf, $childPage, $childAnswer);
        if ($attachment = $answer->getAttachment()) {
          $pdf->addPdf($attachment->getAttachment());
        }
        $pdf->addText("\n", 'p');
      }
    }
  }

  /**
   * Render a single answer in the PDF
   * @param \Jazzee\ApplicantPDF $pdf
   * @param \Jazzee\Entity\Page $page
   * @param \Jazzee\Entity\Answer $answer
   */
  protected function renderPdfAnswerFromArray(\Jazzee\Entity\Page $page, \Jazzee\ApplicantPDF $pdf, array $answerData)
  {
    $value = '';
    foreach($answerData['elements'] as $eArr){
      if($eArr['id'] == 'type'){
        $value = $eArr['displayValue'];
        break;
      }
    }
    if (!empty($value)) {
      $pdf->addText("Type: ", 'b');
      $pdf->addText("{$value}\n", 'p');
    }
    foreach ($page->getElements() as $element) {
      $element->getJazzeeElement()->setController($this->_controller);
      $value = $element->getJazzeeElement()->pdfValueFromArray($answerData, $pdf);
      if (!empty($value)) {
        $pdf->addText("{$element->getTitle()}: ", 'b');
        $pdf->addText("{$value}\n", 'p');
      }
    }
    foreach($page->getChildren() as $child){
      foreach ($child->getElements() as $element) {
        $element->getJazzeeElement()->setController($this->_controller);
        $value = $element->getJazzeeElement()->pdfValueFromArray($answerData, $pdf);
        if (!empty($value)) {
          $pdf->addText("{$element->getTitle()}: ", 'b');
          $pdf->addText("{$value}\n", 'p');
        }
      }
    }
    if ($attachment = $answerData['attachment']) {
      $pdf->addPdf(\Jazzee\Globals::getFileStore()->getFileContents($attachment["attachmentHash"]));
    }
  }

  public static function applyPageElement()
  {
    return 'Education-apply_page';
  }

  public static function pageBuilderScriptPath()
  {
    return 'resource/scripts/page_types/JazzeePageEducation.js';
  }

  public static function applicantsSingleElement()
  {
    return 'Education-applicants_single';
  }

}