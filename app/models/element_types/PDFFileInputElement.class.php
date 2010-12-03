<?php
/**
 * PDFFileInput Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class PDFFileInputElement extends ApplyElement {
  /**
   * Hold the ElementAnswer so we can use it when displaying
   * @param ElementAnswer
   */
  protected $elementAnswer;
  
  public function addToField(Form_Field $field){
    if(!ini_get('file_uploads')){
      throw new Jazzee_Exception('File uploads are not turned on for this system and a PDFFileInputElement is being created', E_USER_ERROR);
    }
    $element = $field->newElement('FileInput', 'el' . $this->element->id);
    $element->label = $this->element->title;
    $element->instructions = $this->element->instructions;
    $element->format = $this->element->format;
    $element->value = $this->element->defaultValue;
    if($this->element->required){
      $element->addValidator('NotEmpty');
    }
    $element->addFilter('Blob');
    $element->addValidator('PDF');
    $config = new ConfigManager;
    if($this->element->max){
      $element->addValidator('MaximumFileSize', $this->element->max);
    } else {
      $element->addValidator('MaximumFileSize', $config->max_apply_file_size);
    }
    return $element;
  }
  
  public function setValueFromInput($input){
    $this->value = $input;
  }
  
  public function setValueFromAnswer($answers){
    if(isset($answers[0])){
      $this->elementAnswer = $answers[0];
      $this->value = $this->elementAnswer->eBlob;
    }
  }
  
  public function getAnswers(){
    if(is_null($this->value)) return array();
    $elementAnswer = new ElementAnswer;
    $elementAnswer->elementID = $this->element->id;
    $elementAnswer->position = 0;
    $elementAnswer->eBlob = $this->value;
    return array($elementAnswer);
  }
  
  public function displayValue(){
    if(is_null($this->value)){
      return null;
    }
    //a unique name which is repeatable
    $name = 'file' . $this->elementAnswer->answerID . $this->elementAnswer->elementID . strtotime($this->elementAnswer->Answer->updatedAt);
    $file = new FileContainer($this->value, 'pdf', $name);
    $file->setLastModified(strtotime($this->elementAnswer->Answer->updatedAt));
    Session::getInstance()->getStore('files')->$name = $file;
    return "<a href='file/{$name}.pdf'>View PDF</a>";
  }
  
  public function formValue(){
    return null;
  }
}
?>