<?php
namespace Jazzee\Element;

/**
 * PDF File Element
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PDFFileInput extends AbstractElement
{

  const PAGEBUILDER_SCRIPT = 'resource/scripts/element_types/JazzeeElementPDFFileInput.js';

  public function addToField(\Foundation\Form\Field $field)
  {
    if (!ini_get('file_uploads')) {
      throw new \Jazzee\Exception('File uploads are not turned on for this system and a PDFFileInputElement is being created', E_ERROR);
    }
    $element = $field->newElement('FileInput', 'el' . $this->_element->getId());
    $element->setLabel($this->_element->getTitle());
    $element->setInstructions($this->_element->getInstructions());
    $element->setFormat($this->_element->getFormat());
    $element->setDefaultValue($this->_element->getDefaultValue());
    if ($this->_element->isRequired()) {
      $validator = new \Foundation\Form\Validator\NotEmpty($element);
      $element->addValidator($validator);
    }

    if ($this->_controller->getConfig()->getVirusScanUploads()) {
      $element->addValidator(new \Foundation\Form\Validator\Virusscan($element));
    }
    $element->addValidator(new \Foundation\Form\Validator\PDF($element));
    $element->addValidator(new \Foundation\Form\Validator\PDFNotEncrypted($element));
    $element->addFilter(new \Foundation\Form\Filter\Blob($element));

    $max = $this->_controller->getConfig()->getMaximumApplicantFileUploadSize();
    if ($this->_element->getMax() and \Foundation\Utility::convertIniShorthandValue($this->_element->getMax()) <= $max) {
      $max = $this->_element->getMax();
    } else {
      $max = $this->_controller->getConfig()->getDefaultApplicantFileUploadSize();
    }
    $element->addValidator(new \Foundation\Form\Validator\MaximumFileSize($element, $max));

    return $element;
  }

  public function getElementAnswers($input)
  {
    $elementAnswers = array();
    if (!is_null($input)) {
      $fileHash = \Jazzee\Globals::getFileStore()->storeFile($input);
      $elementAnswer = new \Jazzee\Entity\ElementAnswer;
      $elementAnswer->setElement($this->_element);
      $elementAnswer->setPosition(0);
      $elementAnswer->setEShortString($fileHash);
      $elementAnswers[] = $elementAnswer;

      $elementAnswer = new \Jazzee\Entity\ElementAnswer;
      $elementAnswer->setElement($this->_element);
      $elementAnswer->setPosition(1);
      $elementAnswers[] = $elementAnswer;
    }

    return $elementAnswers;
  }

  public function displayValue(\Jazzee\Entity\Answer $answer)
  {
    $elementAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementAnswers[0])) {
      $base = $answer->getApplicant()->getFullName() . ' ' . $this->_element->getTitle() . '_' . $answer->getApplicant()->getId() . $elementAnswers[0]->getId();
      //remove slashes in path to fix an apache issues with encoding slashes in redirects
      $base = str_replace(array('/', '\\'),'slash' , $base);
      $pdfName = $base . '.pdf';
      $pngName = $base . 'preview.png';
      \Jazzee\Globals::getFileStore()->createSessionFile($pdfName, $elementAnswers[0]->getEShortString());
      if($elementAnswers[1]->getEShortString() != null){
        \Jazzee\Globals::getFileStore()->createSessionFile($pngName, $elementAnswers[0]->getEShortString());
        $thumbnailPath = \Jazzee\Globals::path('file/' . \urlencode($pngName));
      } else {
        $thumbnailPath = \Jazzee\Globals::path('resource/foundation/media/default_pdf_logo.png');
      }

      return '<a href="' . $this->_controller->path('file/' . \urlencode($pdfName)) . '"><img src="' . $thumbnailPath . '" /></a>';
    }

    return null;
  }

  /**
   * Format element answer data into an array
   * Include links in PDF files for the file and thumbnail
   * 
   * @param array $elementAnswers
   * 
   * @return array
   */
  public function formatApplicantArray(array $elementAnswers)
  {
    $arr = parent::formatApplicantArray($elementAnswers);
    $arr['filePath'] = false;
    $arr['thumbnailPath'] = false;

    foreach($elementAnswers as $elementAnswer){
      $arr['values'][] = $this->arrayValue($elementAnswer);
    }
    if($arr['values'][0]['value']){
      $base = $this->_element->getTitle() . '_' . $elementAnswer['id'];
      //remove slashes in path to fix an apache issues with encoding slashes in redirects
      $base = str_replace(array('/', '\\'),'slash' , $base);

      $name = $base . '.pdf';
      \Jazzee\Globals::getFileStore()->createSessionFile($name, $arr['values'][0]['value']);
      $arr['filePath'] = \Jazzee\Globals::path('file/' . \urlencode($name));
      if (!empty($arr['values'][1]['value'])) {
        $name = $base . '.png';
        \Jazzee\Globals::getFileStore()->createSessionFile($name, $arr['values'][1]['value']);
        $arr['thumbnailPath'] = \Jazzee\Globals::path('file/' . \urlencode($name));
      } else {
        $arr['thumbnailPath'] = \Jazzee\Globals::path('resource/foundation/media/default_pdf_logo.png');
      }
      $arr['displayValue'] = "<a href='{$arr['filePath']}'><img src='{$arr['thumbnailPath']}' /></a>";
    }

    return $arr;
  }
  
  protected function arrayValue(array $elementAnswer){
    $value = array(
      'value' => $elementAnswer['eShortString']
    );
    
    return $value;
  }

  public function rawValue(\Jazzee\Entity\Answer $answer)
  {
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementsAnswers[0])) {
      return base64_encode(\Jazzee\Globals::getFileStore()->getFileContents($elementsAnswers[0]->getEShortString()));
    }

    return null;
  }

  public function pdfValue(\Jazzee\Entity\Answer $answer, \Jazzee\ApplicantPDF $pdf)
  {
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementsAnswers[0])) {
      $pdf->addPdf(\Jazzee\Globals::getFileStore()->getFileContents($elementsAnswers[0]->getEShortString()));

      return 'Attached';
    }

    return null;
  }

  public function pdfValueFromArray(array $answerData, \Jazzee\ApplicantPDF $pdf){
    foreach($answerData['elements'] as $arr){
      if($arr['id'] == $this->_element->getId()){
        $pdf->addPdf(\Jazzee\Globals::getFileStore()->getFileContents($arr["values"][0]["value"]));
        return 'Attached';
      }
    }

    return null;
  }

  /**
   * Get the template pdf values of the element
   * Takes all the answers and returns a single string that sumerizes the data
   *
   * @param array $answers
   * @return string
   */
  public function pdfTemplateValue(array $answers)
  {
    $blob = false;
    if(count($answers)){
      $answer = array_shift($answers);
      $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
      if (isset($elementsAnswers[0])) {
        $blob = \Jazzee\Globals::getFileStore()->getFileContents($elementsAnswers[0]->getEShortString());
      }
    }

    return $blob;
  }

  /**
   * Get the template pdf values of the element from array data
   * Takes all the answers and returns a single string that sumerizes the data
   *
   * @param array $answers
   * @return string
   */
  function pdfTemplateValueFromArray(array $answers)
  {
    $blob = false;
    if(count($answers)){
      $answer = array_shift($answers);
      if(array_key_exists($this->_element->getId(), $answer['elements'])){
        $blob = \Jazzee\Globals::getFileStore()->getFileContents($answer['elements'][$this->_element->getId()][0]['eShortString']);
      }
    }

    return $blob;
  }

  public function formValue(\Jazzee\Entity\Answer $answer)
  {
    return false;
  }

  /**
   * Get the answer value as an xml element
   * Add the PDF size as an attribute
   * @param \DomDocument $dom
   * @param \Jazzee\Entity\Answer $answer
   * @param integer $version
   * @return \DomElement
   */
  public function getXmlAnswer(\DomDocument $dom, \Jazzee\Entity\Answer $answer, $version)
  {
    $eXml = $dom->createElement('element');
    $eXml->setAttribute('elementId', $this->_element->getId());
    $eXml->setAttribute('title', htmlentities($this->_element->getTitle(), ENT_COMPAT, 'utf-8'));
    $eXml->setAttribute('name', htmlentities($this->_element->getName(), ENT_COMPAT, 'utf-8'));
    $eXml->setAttribute('type', htmlentities($this->_element->getType()->getClass(), ENT_COMPAT, 'utf-8'));
    $eXml->setAttribute('weight', $this->_element->getWeight());
    if ($value = $this->rawValue($answer)) {
      $eXml->setAttribute('size', strlen($value));
      $eXml->appendChild($dom->createCDATASection(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $value)));
    }
    return $eXml;
  }

  /**
   * When removing an element answer remove its file association as well
   * @param \Jazzee\Entity\ElementAnswer $elementAnswer
   */
  public function removeElementAnswer(\Jazzee\Entity\ElementAnswer $elementAnswer)
  {
    if($elementAnswer->getEShortString()){
      \Jazzee\Globals::getFileStore()->removeFile($elementAnswer->getEShortString());
    }
  }

  /**
   * PDF File configuration varialbes
   * @param \Jazzee\Configuration $configuration
   * @return array
   */
  public static function getConfigurationVariables(\Jazzee\Configuration $configuration)
  {
    return array(
      'defaultApplicantFileUploadSize' => $configuration->getDefaultApplicantFileUploadSize(),
      'maximumApplicantFileUploadSize' => $configuration->getMaximumApplicantFileUploadSize()
    );
  }

}