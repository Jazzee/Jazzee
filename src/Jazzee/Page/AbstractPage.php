<?php
namespace Jazzee\Page;

/**
 * AbstractPage
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
abstract class AbstractPage implements \Jazzee\Interfaces\Page, \Jazzee\Interfaces\DataPage, \Jazzee\Interfaces\FormPage, \Jazzee\Interfaces\ReviewPage, \Jazzee\Interfaces\PdfPage, \Jazzee\Interfaces\CsvPage, \Jazzee\Interfaces\XmlPage
{

  const ERROR_MESSAGE = 'There was a problem saving your data on this page.  Please correct the errors below and retry your request.';

  /**
   * The ApplicationPage Entity
   * @var \Jazzee\Entity\ApplicationPage
   */
  protected $_applicationPage;

  /**
   * Our controller
   * @var \Jazzee\Controller
   */
  protected $_controller;

  /**
   * The Applicant
   * @var \Jazzee\Entity\Applicant
   */
  protected $_applicant;

  /**
   * Our form
   * @var \Foundation\Form
   */
  protected $_form;

  public function __construct(\Jazzee\Entity\ApplicationPage $applicationPage)
  {
    $this->_applicationPage = $applicationPage;
  }

  public function __destruct()
  {
    $this->_applicationPage = null;
    $this->_applicant = null;
    $this->_controller = null;
    $this->_form = null;
  }

  public function setController(\Jazzee\Controller $controller)
  {
    $this->_controller = $controller;
  }

  public function setApplicant(\Jazzee\Entity\Applicant $applicant)
  {
    $this->_applicant = $applicant;
  }

  public function getForm()
  {
    if (is_null($this->_form)) {
      $this->_form = $this->makeForm();
    }
    //reset the CSRF token on every request so when submission fails token validation doesn't even if the session has timed out
    $this->_form->setCSRFToken($this->_controller->getCSRFToken());

    return $this->_form;
  }

  /**
   * Make the form for the page
   * @return \Foundation\Form
   */
  protected function makeForm()
  {
    $form = new \Foundation\Form;
    $form->setAction($this->_controller->getActionPath());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());
    foreach ($this->_applicationPage->getPage()->getElements() as $element) {
      $element->getJazzeeElement()->setController($this->_controller);
      $element->getJazzeeElement()->addToField($field);
    }
    $form->newButton('submit', 'Save');

    return $form;
  }

  public function validateInput($arr)
  {
    if ($input = $this->getForm()->processInput($arr)) {
      return $input;
    }
    $this->_controller->addMessage('error', self::ERROR_MESSAGE);

    return false;
  }

  public function newAnswer($input)
  {
    if (is_null($this->_applicationPage->getMax()) or count($this->getAnswers()) < $this->_applicationPage->getMax()) {
      $answer = new \Jazzee\Entity\Answer();
      $answer->setPage($this->_applicationPage->getPage());
      $this->_applicant->addAnswer($answer);
      foreach ($this->_applicationPage->getPage()->getElements() as $element) {
        $element->getJazzeeElement()->setController($this->_controller);
        foreach ($element->getJazzeeElement()->getElementAnswers($input->get('el' . $element->getId())) as $elementAnswer) {
          $answer->addElementAnswer($elementAnswer);
        }
      }
      $this->getForm()->applyDefaultValues();
      $this->_controller->getEntityManager()->persist($answer);
      $this->_controller->addMessage('success', 'Answer Saved Successfully');
      //flush here so the answerId will be correct when we view
      $this->_controller->getEntityManager()->flush();
    }
  }

  public function updateAnswer($input, $answerId)
  {
    if ($answer = $this->_applicant->findAnswerById($answerId)) {
      foreach ($answer->getElementAnswers() as $ea) {
        $answer->getElementAnswers()->removeElement($ea);
        $this->_controller->getEntityManager()->remove($ea);
      }
      foreach ($this->_applicationPage->getPage()->getElements() as $element) {
        $element->getJazzeeElement()->setController($this->_controller);
        foreach ($element->getJazzeeElement()->getElementAnswers($input->get('el' . $element->getId())) as $elementAnswer) {
          $answer->addElementAnswer($elementAnswer);
        }
      }
      $this->getForm()->applyDefaultValues();
      $this->getForm()->setAction($this->_controller->getActionPath());
      $this->_controller->getEntityManager()->persist($answer);
      $this->_controller->addMessage('success', 'Answer Updated Successfully');
    }
  }

  public function deleteAnswer($answerId)
  {
    if ($answer = $this->_applicant->findAnswerById($answerId)) {
      $this->_controller->getEntityManager()->remove($answer);
      $this->_applicant->getAnswers()->removeElement($answer);
      $this->_applicant->markLastUpdate();
      $this->_controller->getEntityManager()->persist($this->_applicant);
      $this->_controller->addMessage('success', 'Answered Deleted Successfully');
    }
  }

  public function fill($answerId)
  {
    if ($answer = $this->_applicant->findAnswerById($answerId)) {
      foreach ($this->_applicationPage->getPage()->getElements() as $element) {
        $element->getJazzeeElement()->setController($this->_controller);
        $value = $element->getJazzeeElement()->formValue($answer);
        if ($value) {
          $this->getForm()->getElementByName('el' . $element->getId())->setValue($value);
        }
      }
      $this->getForm()->setAction($this->_controller->getActionPath() . "/edit/{$answerId}");
    }
  }

  /**
   * Get all the answers for this page
   * @return \Jazzee\Entity\Answer
   */
  public function getAnswers()
  {
    return $this->_applicant->findAnswersByPage($this->_applicationPage->getPage());
  }

  public function getXmlAnswers(\DOMDocument $dom, $version)
  {
    $answers = array();
    foreach ($this->_applicant->findAnswersByPage($this->_applicationPage->getPage()) as $answer) {
      $answers[] = $this->xmlAnswer($dom, $answer, $version);
    }

    return $answers;
  }

  /**
   * Most pages don't require any setup
   *
   */
  public function setupNewPage()
  {
    return;
  }

  /**
   * Default CSV headers are just the elements for a page
   * @return array
   */
  public function getCsvHeaders()
  {
    $headers = array();
    foreach ($this->_applicationPage->getPage()->getElements() as $element) {
      $headers[] = $element->getTitle();
    }

    return $headers;
  }

  /**
   * Defaults to just usign the element display values
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

    return $arr;
  }

  /**
   * Convert an answer to an xml element
   * @param \DomDocument $dom
   * @param \Jazzee\Entity\Answer $answer
   * @param integer $version the XML version to create
   * @return \DomElement
   */
  protected function xmlAnswer(\DomDocument $dom, \Jazzee\Entity\Answer $answer, $version)
  {
    $answerXml = $dom->createElement('answer');
    $answerXml->setAttribute('answerId', $answer->getId());
    $answerXml->setAttribute('uniqueId', $answer->getUniqueId());
    $answerXml->setAttribute('updatedAt', $answer->getUpdatedAt()->format('c'));
    $answerXml->setAttribute('pageStatus', $answer->getPageStatus());
    $answerXml->setAttribute('publicStatus', ($answer->getPublicStatus() ? $answer->getPublicStatus()->getName() : ''));
    $answerXml->setAttribute('privateStatus', ($answer->getPrivateStatus() ? $answer->getPrivateStatus()->getName() : ''));
    foreach ($answer->getPage()->getElements() as $element) {
      $element->getJazzeeElement()->setController($this->_controller);
      if ($element->getJazzeeElement() instanceof \Jazzee\Interfaces\XmlElement) {
        $answerXml->appendChild($element->getJazzeeElement()->getXmlAnswer($dom, $answer, $version));
      }
    }
    $attachment = $dom->createElement('attachment');
    if ($answer->getAttachment()) {
      $attachment->appendChild($dom->createCDATASection(base64_encode($answer->getAttachment()->getAttachment())));
    }
    $answerXml->appendChild($attachment);

    $children = $dom->createElement('children');
    foreach ($answer->getChildren() as $child) {
      $children->appendChild($this->xmlAnswer($dom, $child, $version));
    }
    $answerXml->appendChild($children);

    return $answerXml;
  }
  
  /**
   * Format a page array
   * @param array $answers
   * 
   * @return array
   */
  public function formatApplicantArray(array $answers)
  {
    $page = array(
      'title' => $this->_applicationPage->getTitle(),
      'type' => $this->_applicationPage->getPage()->getType()->getClass(),
      'name' => $this->_applicationPage->getName(),
      'id' => $this->_applicationPage->getPage()->getId(),
      'status' => $this->getArrayStatus($answers),
      'answers' => array()
    );
    
    foreach($answers as $answer){
      $page['answers'][] = $this->arrayAnswer($answer, $this->_applicationPage->getPage());
    }

    return $page;
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
    
    $children = $answer['children'];
    $answer['children'] = array();

    foreach ($children as $child) {
      $answer['children'][] = $this->arrayAnswer($child, $page->getChildById($child['page_id']));
    }
    if(!is_null($answer['attachment'])){
      $answer['attachment'] = $this->arrayAnswerAttachment($answer['attachment'], $page);
    }

    return $answer;
  }

  /**
   * Format an answer attachment
   * @param \array $attachment
   * @param \Jazzee\Entity\Page $page
   * 
   * @return array
   */
  protected function arrayAnswerAttachment(array $attachment, \Jazzee\Entity\Page $page)
  {
    $attachment['filePath'] = false;
    $attachment['thumbnailPath'] = false;

    $base = $page->getTitle() . '_attachment_' . $attachment['id'];
    //remove slashes in path to fix an apache issues with encoding slashes in redirects
    $base = str_replace(array('/', '\\'),'slash' , $base);
    $pdfName = $base . '.pdf';
    $pngName = $base . 'preview.png';
    \Jazzee\Globals::getFileStore()->createSessionFile($pdfName, $attachment['attachmentHash']);
    $attachment['filePath'] = \Jazzee\Globals::path('file/' . \urlencode($pdfName));
    if (!empty($attachment['thumbnailHash'])) {
      \Jazzee\Globals::getFileStore()->createSessionFile($pngName, $arr['thumbnailHash']);
      $attachment['thumbnailPath'] = \Jazzee\Globals::path('file/' . \urlencode($pngName));
    } else {
      $attachment['thumbnailPath'] = \Jazzee\Globals::path('resource/foundation/media/default_pdf_logo.png');
    }
    $attachment['displayValue'] = "<a href='{$attachment['filePath']}'><img src='{$attachment['thumbnailPath']}' /></a>";

    return $attachment;
  }
  
  /**
   * Get the status of the page from the answer array
   * @param array $answers
   * @return int
   */
  abstract protected function getArrayStatus(array $answers);

  /**
   * Create a table from answers
   * and append any attached PDFs
   * @param \Jazzee\ApplicantPDF $pdf
   */
  public function renderPdfSection(\Jazzee\ApplicantPDF $pdf)
  {
    $pdf->addText($this->_applicationPage->getTitle() . "\n", 'h3');
    if ($this->getStatus() == \Jazzee\Interfaces\Page::SKIPPED) {
      $pdf->addText("Applicant Skipped this page.\n", 'p');
    } else {
      foreach ($this->getAnswers() as $answer) {
        $this->renderPdfAnswer($pdf, $this->_applicationPage->getPage(), $answer);
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
  protected function renderPdfAnswer(\Jazzee\ApplicantPDF $pdf, \Jazzee\Entity\Page $page, \Jazzee\Entity\Answer $answer)
  {
    foreach ($page->getElements() as $element) {
      $element->getJazzeeElement()->setController($this->_controller);
      $value = $element->getJazzeeElement()->pdfValue($answer, $pdf);
      if (!empty($value)) {
        $pdf->addText("{$element->getTitle()}: ", 'b');
        $pdf->addText("{$value}\n", 'p');
      }
    }
    if(!$pdf instanceof \Jazzee\RestrictedPDF){
      if ($attachment = $answer->getAttachment()) {
        $pdf->addPdf($attachment->getAttachment());
      }
    }
  }

  public function renderPdfSectionFromArray(array $answers, \Jazzee\ApplicantPDF $pdf)
  {
    $pdf->addText($this->_applicationPage->getTitle() . "\n", 'h3');
    if (!$this->_applicationPage->isRequired() and count($answers) and $this->getArrayStatus($answers) == self::SKIPPED) {
      $pdf->addText("Applicant Skipped this page.\n", 'p');
    }else{
      foreach ($answers as $answer) {
        $this->renderPdfAnswerFromArray($this->_applicationPage->getPage(), $pdf, $answer);
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
    foreach ($page->getElements() as $element) {
      $element->getJazzeeElement()->setController($this->_controller);
      $value = $element->getJazzeeElement()->pdfValueFromArray($answerData, $pdf);
      if (!empty($value)) {
        $pdf->addText("{$element->getTitle()}: ", 'b');
        $pdf->addText("{$value}\n", 'p');
      }
    }

    if ($attachment = $answerData['attachment']) {
      $pdf->addPdf(\Jazzee\Globals::getFileStore()->getFileContents($attachment["attachmentHash"]));
    }
  }
  
  /**
   * Get the values for each element for use in the PDF template
   * @return array
   */
  public function getPdfTemplateValues()
  {
    $values = array();
    foreach($this->_applicationPage->getPage()->getElements() as $element){
      $values[$element->getId()] = $element->getJazzeeElement()->pdfTemplateValue($this->getAnswers());
    }

    return $values;
  }

  public function formatApplicantPDFTemplateArray(array $answers)
  {
    $values = array();
    foreach($this->_applicationPage->getPage()->getElements() as $element){
      $values[$element->getId()] = $element->getJazzeeElement()->pdfTemplateValueFromArray($answers);
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
    foreach($this->_applicationPage->getPage()->getElements() as $element){
      $templateElements['page-'.$this->_applicationPage->getPage()->getId() . '-element-' . $element->getId()] = $this->_applicationPage->getTitle() . ': ' . substr($element->getTitle(), 0, 64);;
    }

    return $templateElements;
  }

  /**
   * By default just set the varialbe dont check it
   * @param string $name
   * @param string $value
   */
  public function setVar($name, $value)
  {
    $var = $this->_applicationPage->getPage()->setVar($name, $value);
    $this->_controller->getEntityManager()->persist($var);
  }

  /**
   * Check if the current controller is an admin controller
   * @throws \Jazzee\Exception if it isn't
   */
  protected function checkIsAdmin()
  {
    if ($this->_controller instanceof \Jazzee\AdminController) {
      return true;
    }
    throw new \Jazzee\Exception('Admin only action was called from a non admin controller');
  }

  /**
   * Compare this page to another page and list the differences
   *
   * @param \Jazzee\Entity\ApplicationPage $applicationPage
   */
  public function compareWith(\Jazzee\Entity\ApplicationPage $applicationPage)
  {
    $differences = array(
      'different' => false,
      'title' => $this->_applicationPage->getTitle(),
      'properties' => array(),
      'elements' => array(
        'new' => array(),
        'removed' => array(),
        'same' => array(),
        'changed' => array()
      ),
      'children' => array(
        'new' => array(),
        'removed' => array(),
        'same' => array(),
        'changed' => array()
      )
    );
    $arr = array(
      'title' => 'Title',
      'name' => 'Name',
      'min' => 'Minimum Answers',
      'max' => 'Maximum Answers',
      'instructions' => 'Instructions',
      'leadingText' => 'Leading Text',
      'trailingText' => 'Trailing Text'
    );
    foreach ($arr as $name => $niceName) {
      $func = 'get' . ucfirst($name);
      if ($this->_applicationPage->$func() != $applicationPage->$func()) {
        $differences['different'] = true;
        $differences['properties'][] = array(
          'name' => $niceName,
          'type' => 'textdiff',
          'this' => $this->_applicationPage->$func(),
          'other' => $applicationPage->$func()
        );
      }
    }
    $thisElements = array();
    foreach ($this->_applicationPage->getPage()->getElements() as $element) {
      $thisElements[$element->getTitle()] = $element;
    }
    $otherElements = array();
    foreach ($applicationPage->getPage()->getElements() as $element) {
      $otherElements[$element->getTitle()] = $element;
    }
    foreach ($thisElements as $title => $element) {
      if (!array_key_exists($title, $otherElements)) {
        $differences['different'] = true;
        $differences['elements']['new'][] = $title;
      } else if ($element->getType()->getId() != $otherElements[$title]->getType()->getId()) {
        $differences['different'] = true;
        $differences['elements']['new'][] = $title;
        $differences['elements']['removed'][] = $title;
      } else {
        $elementDifferences = $element->getJazzeeElement()->compareWith($otherElements[$title]);
        if ($elementDifferences['different']) {
          $differences['different'] = true;
          $differences['elements']['changed'][] = $elementDifferences;
        }
      }
    }
    foreach ($otherElements as $title => $element) {
      if (!array_key_exists($title, $thisElements)) {
        $differences['different'] = true;
        $differences['elements']['removed'][] = $title;
      }
    }
    
    $thisChildren = array();
    foreach($this->_applicationPage->getPage()->getChildren() as $childPage){
      $thisChildren[$childPage->getTitle()] = $childPage;
    }
    $otherChildren = array();
    foreach($applicationPage->getPage()->getChildren() as $childPage){
      $otherChildren[$childPage->getTitle()] = $childPage;
    }
    foreach($thisChildren as $title => $childPage){
      $pageTitle = $title;
      if(!array_key_exists($title, $otherChildren)){
        $differences['different'] = true;
        $differences['children']['new'][] = $pageTitle;
      } else if($childPage->getType()->getId() != $otherChildren[$title]->getType()->getId()){
        $differences['different'] = true;
        $differences['children']['new'][] = $pageTitle;
        $differences['children']['removed'][] = $pageTitle;
      } else {
        $pageDifferences = $childPage->getApplicationPageJazzeePage()->compareWith($otherChildren[$title]->getFakeApplicationPage());
        if($pageDifferences['different']){
          $differences['different'] = true;
          $differences['children']['changed'][] = $pageDifferences;
        }
      }
    }
    foreach($otherChildren as $title => $array){
      if(!array_key_exists($title, $thisChildren)){
        $differences['different'] = true;
        $differences['children']['removed'][] = $title;
      }
    }
    return $differences;
  }

  /**
   * Get page properties
   *
   * Lists all the properties of a page in an array so it can be compared to other
   * pages and accross cycles
   *
   * @return array
   */
  public function listProperties()
  {
    $properties = array();
    $arr = array(
      'title',
      'name',
      'min',
      'max',
      'weight',
      'instructions',
      'leadingText',
      'trailingText'
    );
    foreach ($arr as $name) {
      $func = 'get' . ucfirst($name);
      $properties[$name] = $this->_applicationPage->$func();
    }
    if ($this->_applicationPage->isRequired()) {
      $properties['isRequired'] = true;
    } else {
      $properties['isRequired'] = false;
    }
    if ($this->_applicationPage->showAnswerStatus()) {
      $properties['answerStatusDisplay'] = true;
    } else {
      $properties['answerStatusDisplay'] = false;
    }
    $properties['variables'] = array();
    foreach ($this->_applicationPage->getPage()->getVariables() as $var) {
      $properties['variables'][$var->getName()] = $var->getValue();
    }
    $properties['elements'] = array();
    foreach ($this->_applicationPage->getPage()->getElements() as $element) {
      $properties['elements'][$element->getTitle()] = $element->getJazzeeElement()->listProperties();
    }
    return $properties;
  }
  
  /**
   * By default list the elements on a page
   * 
   * @return array
   */
  public function listDisplayElements()
  {
    $elements = array();
    $weight = 0;
    foreach($this->_applicationPage->getPage()->getElements() as $element){
      $elements[] = new \Jazzee\Display\Element('element', $element->getTitle(), $weight++, $element->getId(), null);
    }
    $elements[] = new \Jazzee\Display\Element('page', $this->_applicationPage->getTitle() . ' Attacment', $weight++, 'attachment', $this->_applicationPage->getPage()->getId());
    $elements[] = new \Jazzee\Display\Element('page', $this->_applicationPage->getTitle() . ' Public Answer Status', $weight++, 'publicAnswerStatus', $this->_applicationPage->getPage()->getId());
    $elements[] = new \Jazzee\Display\Element('page', $this->_applicationPage->getTitle() . ' Private Answer Status', $weight++, 'privateAnswerStatus', $this->_applicationPage->getPage()->getId());

    return $elements;
  }

}