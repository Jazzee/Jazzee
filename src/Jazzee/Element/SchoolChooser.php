<?php

namespace Jazzee\Element;

/**
 * SchoolChooser Element
 * Autocomplete school names
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class SchoolChooser extends TextInput
{

  const PAGEBUILDER_SCRIPT = 'resource/scripts/element_types/JazzeeElementSchoolChooser.js';

  public function addToField(\Foundation\Form\Field $field)
  {

    $element = parent::addToField($field);

    $element->addClass("school-chooser");

/*
    $validator = new \Foundation\Form\Validator\Regex($element, '/^(?!000)([0-6]\d{2}|7([0-6]\d|7[012]))([ -]?)(?!00)\d\d([ -]?)(?!0000)\d{4}$/');
    $validator->setErrorMessage('This is not a valid US Social Secutiry Number');
    $element->addValidator($validator);
    $element->prependFilter(new \Foundation\Form\Filter\Replace($element, array('pattern'=>'/[^0-9]/', 'replace'=>'')));
*/
    return $element;
  }


  public function getElementAnswers($input)
  {
    $elementAnswers = array();
    if (!is_null($input)) {
      
      $fullName = $_POST["el".$this->_element->getId()];

            
      $schools = null;
      if(strpos($fullName, ";")){
	$parts = explode(";", $fullName);
	$schools = $this->_controller->getEntityManager()->getRepository('Jazzee\Entity\School')->findByFullName($parts[0]); 
      }else{
	$schools = $this->_controller->getEntityManager()->getRepository('Jazzee\Entity\School')->findByFullName($fullName); 
      }

      if(count($schools) == 0){
	try{	  
	  $parts = explode(";", $fullName);
	  $ns = new \Jazzee\Entity\School;
	  $ns->setFullName($parts[0]);
	  $ns->setAddress1($parts[1]);
	  $ns->setCity($parts[2]);
	  $ns->setState($parts[3]);
	  $ns->setZip($parts[4]);
	  $ns->setCountry($parts[5]);

	  $this->_controller->getEntityManager()->persist($ns);		  
	  $this->_controller->getEntityManager()->flush();	
	   
	}catch(Exception $noAdd){
	  error_log("unable to add new school");
	  $this->addMessage('error', "Unable to add new school.");
	  $this->redirectApplyFirstPage();
	}		

	$schools = $this->_controller->getEntityManager()->getRepository('Jazzee\Entity\School')->findByFullName($parts[0]); 
	
      }
      $elementAnswer = new \Jazzee\Entity\ElementAnswer;
      $elementAnswer->setElement($this->_element);
      $elementAnswer->setPosition(0);
      $elementAnswer->setEInteger($schools[0]['id']);
      $elementAnswers[] = $elementAnswer;
    }

    return $elementAnswers;
  }


  public function displayValue(\Jazzee\Entity\Answer $answer)
  {
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementsAnswers[0])) {
      $sid = $elementsAnswers[0]->getEInteger();	
      $school = $this->_controller->getEntityManager()->getRepository('Jazzee\Entity\School')->findById($sid); 
      return $school["fullName"];
    }

    return null;
  }


  public function formValue(\Jazzee\Entity\Answer $answer)
  {
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementsAnswers[0])) {
      $sid = $elementsAnswers[0]->getEInteger();	
      $school = $this->_controller->getEntityManager()->getRepository('Jazzee\Entity\School')->findById($sid); 
      return $school["fullName"];
    }

    return null;
  }

}