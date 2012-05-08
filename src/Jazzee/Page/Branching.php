<?php
namespace Jazzee\Page;
/**
 * Branch a child page depending on an applicant input
 * 
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage pages
 */
class Branching extends Standard 
{
  
  const APPLY_PAGE_ELEMENT = 'Branching-apply_page';
  const APPLICANTS_SINGLE_ELEMENT = 'Branching-applicants_single';
  const PAGEBUILDER_SCRIPT = 'resource/scripts/page_types/JazzeePageBranching.js';
  /**
   * 
   * Enter description here ...
   */
  protected function makeForm(){
    $form = new \Foundation\Form;
    $form->setCSRFToken($this->_controller->getCSRFToken());
    $form->setAction($this->_controller->getActionPath());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());
    
    $element = $field->newElement('SelectList', 'branching');
    $element->setLabel($this->_applicationPage->getPage()->getVar('branchingElementLabel'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    foreach($this->_applicationPage->getPage()->getChildren() as $child){
      $element->newItem($child->getId(), $child->getTitle());
    }
    $form->newHiddenElement('level', 1);
    $form->newButton('submit', 'Next');
    return $form;
  }
  
  protected function branchingForm($branchingPageID){
    $page = $this->_applicationPage->getPage()->getChildById($branchingPageID);
    
    $form = new \Foundation\Form;
    $form->setAction($this->_controller->getActionPath());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());
    
    foreach($page->getElements() as $element){
      $element->getJazzeeElement()->setController($this->_controller);
      $element->getJazzeeElement()->addToField($field);
    }
    $form->newHiddenElement('level', 2);
    $form->newHiddenElement('branching', $branchingPageID);
    $form->newButton('submit', 'Save');
    
    $this->_form = $form;
  }
  
  public function validateInput($input){
    $this->branchingForm($input['branching']);
    if($input['level'] == 1) return false;
    return parent::validateInput($input);
  }
  
  public function newAnswer($input){
    if(is_null($this->_applicationPage->getMax()) or count($this->getAnswers()) < $this->_applicationPage->getMax()){
      $answer = new \Jazzee\Entity\Answer();
      $answer->setPage($this->_applicationPage->getPage());
      $this->_applicant->addAnswer($answer);
      $childAnswer = new \Jazzee\Entity\Answer;
      $childAnswer->setPage($answer->getPage()->getChildById($input->get('branching')));
      $answer->addChild($childAnswer);
      
      foreach($this->_applicationPage->getPage()->getChildById($input->get('branching'))->getElements() as $element){
        foreach($element->getJazzeeElement()->getElementAnswers($input->get('el'.$element->getId())) as $elementAnswer){
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
  
  public function updateAnswer($input, $answerId){
    if($answer = $this->_applicant->findAnswerById($answerId)){
      foreach($answer->getElementAnswers() as $ea){
        $this->_controller->getEntityManager()->remove($ea);
        $answer->getElementAnswers()->removeElement($ea);
      }
      foreach($answer->getChildren() as $childAnswer){
        $this->_controller->getEntityManager()->remove($childAnswer);
        $answer->getChildren()->removeElement($childAnswer);
      }
      $childAnswer = new \Jazzee\Entity\Answer;
      $childAnswer->setPage($answer->getPage()->getChildById($input->get('branching')));
      $answer->addChild($childAnswer);
      foreach($this->_applicationPage->getPage()->getChildById($input->get('branching'))->getElements() as $element){
        foreach($element->getJazzeeElement()->getElementAnswers($input->get('el'.$element->getId())) as $elementAnswer){
          $childAnswer->addElementAnswer($elementAnswer);
        }
      }
      $this->_form = null;
      $this->_controller->getEntityManager()->persist($answer);
      $this->_controller->getEntityManager()->persist($childAnswer);
      $this->_controller->addMessage('success', 'Answer Updated Successfully');
    }
  }
  
  public function fill($answerId){
    if($answer = $this->_applicant->findAnswerById($answerId)){
      $child = $answer->getChildren()->first();
      $this->branchingForm($child->getPage()->getId());
      foreach($child->getPage()->getElements() as $element){
        $element->getJazzeeElement()->setController($this->_controller);
        $value = $element->getJazzeeElement()->formValue($child);
        if($value) $this->getForm()->getElementByName('el' . $element->getId())->setValue($value);
      }
      $this->getForm()->setAction($this->_controller->getActionPath() . "/edit/{$answerId}");
    }
  }
  
  public function getXmlAnswers(\DOMDocument $dom){
    $answers = array();
    foreach($this->_applicant->findAnswersByPage($this->_applicationPage->getPage()) as $answer){
      $child = $answer->getChildren()->first();
      $xmlAnswer = $this->xmlAnswer($dom, $child);
      $eXml = $dom->createElement('element');
      $eXml->setAttribute('elementId', 'branching');
      
      
      $eXml->setAttribute('title', htmlentities($answer->getPage()->getVar('branchingElementLabel'),ENT_COMPAT,'utf-8'));
      $eXml->setAttribute('type', null);
      $eXml->appendChild($dom->createCDATASection($child->getPage()->getTitle()));
      $xmlAnswer->appendChild($eXml);
      $answers[] = $xmlAnswer;
    }
    return $answers;
  }
  
  /**
   * Branchign pages get special CSV headers so all the branches are reprsented
   * @return array 
   */
  public function getCsvHeaders(){
    $headers = array();
    $headers[] = $this->_applicationPage->getPage()->getVar('branchingElementLabel');
    foreach($this->_applicationPage->getPage()->getChildren() as $child){
      foreach($child->getElements() as $element){
        $headers[] = $child->getTitle() . ' ' . $element->getTitle();
      }
    }
    return $headers;
  }
  
  /**
   * Branching pages return elements for every page
   * @param int $position
   * @return array
   */
  function getCsvAnswer($position){
    $arr = array();
    $answers = $this->_applicant->findAnswersByPage($this->_applicationPage->getPage());
    if(isset($answers[$position])){
      $arr[] = $answers[$position]->getChildren()->first()->getPage()->getTitle();
    }
    foreach($this->_applicationPage->getPage()->getChildren() as $child){
      foreach($child->getElements() as $element){
        $element->getJazzeeElement()->setController($this->_controller);
        if(isset($answers[$position]) and $child == $answers[$position]->getChildren()->first()->getPage()){
          $arr[] = $element->getJazzeeElement()->displayValue($answers[$position]->getChildren()->first());
        } else {
          $arr[] = '';
        }
      }
    }
    return $arr;
  }
  
  /**
   * Setup the default variables
   */
  public function setupNewPage(){
    $defaultVars = array(
      'branchingElementLabel' => ''
    );
    foreach($defaultVars as $name=>$value){
      $var = $this->_applicationPage->getPage()->setVar($name, $value);
      $this->_controller->getEntityManager()->persist($var);
    }    
  }
  
  /**
   * Create a table from answers
   * and append any attached PDFs
   * @param \Jazzee\ApplicantPDF $pdf 
   */
  public function renderPdfSection(\Jazzee\ApplicantPDF $pdf){
    if($this->getAnswers()){
      $pdf->addText($this->_applicationPage->getTitle(), 'h3');
      $pdf->write();
      $pdf->startTable();
      $pdf->startTableRow();
      $pdf->addTableCell($this->_applicationPage->getPage()->getVar('branchingElementLabel'));
      $pdf->addTableCell('Answer');
      foreach($this->getAnswers() as $answer){
        $pdf->startTableRow();
        $child = $answer->getChildren()->first();
        $pdf->addTableCell($child->getPage()->getTitle());
        $string = '';
        foreach($child->getPage()->getElements() as $element){
          $element->getJazzeeElement()->setController($this->_controller);
          $string .= $element->getTitle() . ': ' . $element->getJazzeeElement()->pdfValue($child, $pdf) . "\n";
        }
        $pdf->addTableCell($string);
        if($attachment = $answer->getAttachment()) $pdf->addPdf($attachment->getAttachment());
      }
      $pdf->writeTable();
    }
  }
}