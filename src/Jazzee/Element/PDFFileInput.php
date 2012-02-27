<?php
namespace Jazzee\Element;
/**
 * PDF File Element
 * 
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage elements
 */
class PDFFileInput extends AbstractElement {
  const PAGEBUILDER_SCRIPT = 'resource/scripts/element_types/JazzeeElementPDFFileInput.js';
  public function addToField(\Foundation\Form\Field $field){
    if(!ini_get('file_uploads')){
      throw new \Jazzee\Exception('File uploads are not turned on for this system and a PDFFileInputElement is being created', E_ERROR);
    }
    $element = $field->newElement('FileInput', 'el' . $this->_element->getId());
    $element->setLabel($this->_element->getTitle());
    $element->setInstructions($this->_element->getInstructions());
    $element->setFormat($this->_element->getFormat());
    $element->setDefaultValue($this->_element->getDefaultValue());
    if($this->_element->isRequired()){
      $validator = new \Foundation\Form\Validator\NotEmpty($element);
      $element->addValidator($validator);
    }
    $element->addValidator(new \Foundation\Form\Validator\Virusscan($element));
    $element->addValidator(new \Foundation\Form\Validator\PDF($element));
    $element->addFilter(new \Foundation\Form\Filter\Blob($element));
    
    $config = $this->_controller->getConfig();
    if($config->getMaximumApplicantFileUploadSize()) $max = $config->getMaximumApplicantFileUploadSize();
    else $max = \convertIniShorthandValue(\ini_get('upload_max_filesize'));
    if($this->_element->getMax() and \convertIniShorthandValue($this->_element->getMax()) < $max) $max = $this->_element->getMax();
    
    $element->addValidator(new \Foundation\Form\Validator\MaximumFileSize($element, $max));
    
    return $element;
  }
  
  public function getElementAnswers($input){
    $elementAnswers = array();
    if(!is_null($input)){
      $elementAnswer = new \Jazzee\Entity\ElementAnswer;
      $elementAnswer->setElement($this->_element);
      $elementAnswer->setPosition(0);
      $elementAnswer->setEBlob($input);
      $elementAnswers[] = $elementAnswer;
      
      //create the preview image
      try{
        $im = new \imagick;
        $im->readimageblob($input);
        $im->setiteratorindex(0);
        $im->setImageFormat("png");
        $im->scaleimage(100, 0);
      } catch (ImagickException $e){
        $im = new \imagick;
        $im->readimage(realpath(__DIR__ . '/../../../../lib/foundation/src/media/default_pdf_logo.png'));
        $im->scaleimage(100, 0);
      }
      $elementAnswer = new \Jazzee\Entity\ElementAnswer;
      $elementAnswer->setElement($this->_element);
      $elementAnswer->setPosition(1);
      $elementAnswer->setEBlob($im->getimageblob());
      $elementAnswers[] = $elementAnswer;
    }
    return $elementAnswers;
  }
  
  public function displayValue(\Jazzee\Entity\Answer $answer){
    $elementAnswers = $answer->getElementAnswersForElement($this->_element);
    if(isset($elementAnswers[0])){
      $base = $answer->getApplicant()->getFullName() . ' ' . $this->_element->getTitle() . '_' . $answer->getApplicant()->getId() . $elementAnswers[0]->getId();
      $pdfName =  $base . '.pdf';
      $pngName = $base . 'preview.png';
      if(!$pdfFile = $this->_controller->getStoredFile($pdfName) or $pdfFile->getLastModified() < $answer->getUpdatedAt()){
        $this->_controller->storeFile($pdfName, $elementAnswers[0]->getEBlob());
      }
      if(!$pngFile = $this->_controller->getStoredFile($pngName) or $pngFile->getLastModified() < $answer->getUpdatedAt()){
        $this->_controller->storeFile($pngName, $elementAnswers[1]->getEBlob());
      }
      return '<a href="' . $this->_controller->path('file/' . \urlencode($pdfName)) . '"><img src="' . $this->_controller->path('file/' . \urlencode($pngName)) . '" /></a>';
    }
    return null;
  }
  
  public function rawValue(\Jazzee\Entity\Answer $answer){
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if(isset($elementsAnswers[0])){
      return base64_encode($elementsAnswers[0]->getEBlob());
    }
    return null;
  }
  
  public function pdfValue(\Jazzee\Entity\Answer $answer, \Jazzee\ApplicantPDF $pdf){
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if(isset($elementsAnswers[0])){
      $pdf->addPdf($elementsAnswers[0]->getEBlob());
      return 'Attached';
    }
    return null;
  }
  
  public function formValue(\Jazzee\Entity\Answer $answer){
    return false;
  }
}
?>