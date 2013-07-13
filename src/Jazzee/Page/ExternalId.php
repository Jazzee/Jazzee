<?php
namespace Jazzee\Page;

/**
 * Allows an applicant to set/edit their own external ID
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ExternalId implements \Jazzee\Interfaces\Page, \Jazzee\Interfaces\FormPage
{

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
    $this->_applicationPage->setMin(1);
    $this->_applicationPage->setMax(1);
  }

  /**
   * Setup the default variables
   */
  public function setupNewPage()
  {
    $defaultVars = array(
      'externalIdLabel' => ''
    );
    foreach ($defaultVars as $name => $value) {
      $var = $this->_applicationPage->getPage()->setVar($name, $value);
      $this->_controller->getEntityManager()->persist($var);
    }
  }
  
  public function newAnswer($input)
  {
    $this->_applicant->setExternalId($input->get('externalId'));

    $this->_controller->getEntityManager()->persist($this->_applicant);
    $this->_controller->getEntityManager()->flush();
    $this->_controller->setVar('edit', false);
	
  }

  public function updateAnswer($input, $answerId)
  {
    $this->newAnswer($input);
    $this->_controller->setVar('edit', false);
  }

  public function deleteAnswer($answerId)
  {
    $this->_applicant->setExternalId(null);

    $this->_controller->getEntityManager()->persist($this->_applicant);
    $this->_controller->getEntityManager()->flush();

  }


  /**
   * We don't need to fill since the form fills itself form the applicant
   * @param type $answerId
   */
  public function fill($answerId){
    $this->_controller->setVar('edit', true);
  }

  public static function applicantsSingleElement()
  {
    return 'ExternalId-applicants-single';
  }


  public function getStatus()
  {
    $answers = $this->_applicant->findAnswersByPage($this->_applicationPage->getPage());
    if (!$this->_applicationPage->isRequired() and count($answers) and $answers[0]->getPageStatus() == self::SKIPPED) {
      return self::SKIPPED;
    }
    if ($this->_applicant->getExternalId()) {
      return self::COMPLETE;
    }

    return self::INCOMPLETE;
  }

  public function getArrayStatus(array $answers)
  {
    if (!$this->_applicationPage->isRequired() and count($answers) and $answers[0]['pageStatus'] == self::SKIPPED) {
      return self::SKIPPED;
    }
    if ($this->_applicant->getExternalId()) {
      return self::COMPLETE;
    }

    return self::INCOMPLETE;
  }


  public static function applyPageElement()
  {
    return 'ExternalId-apply_page';
  }

  public static function pageBuilderScriptPath()
  {
    return 'resource/scripts/page_types/JazzeePageExternalId.js';
  }

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

    return $differences;
  }

  /**
   * Get the form or make it if it doesn't exist
   * @return \Foundation\Form
   */
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
  public function makeForm()
  {
    $form = new \Foundation\Form;
    $form->setCSRFToken($this->_controller->getCSRFToken());
    $form->setAction($this->_controller->getActionPath());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());
    $element = $field->newElement('TextInput', 'externalId');
    $element->setLabel($this->_applicationPage->getPage()->getVar('externalIdLabel'));
    $element->setValue($this->_applicant->getExternalId());
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));$element->addValidator(new \Foundation\Form\Validator\SpecialObject($element, array(
      'object' => $this->_applicationPage->getApplication(),
      'method' => 'validateExternalId',
      'errorMessage' => 'This is not a valid External ID.'
    )));
    $form->newButton('submit', 'Save');

    return $form;
  }

  /**
   * Set the applicant
   * @param \Jazzee\Entity\Applicant $applicant
   */
  public function setApplicant(\Jazzee\Entity\Applicant $applicant)
  {
    $this->_applicant = $applicant;
  }

  /**
   * Set the controller
   * @param \Jazzee\Controller $controller
   */
  public function setController(\Jazzee\Controller $controller)
  {
    $this->_controller = $controller;
  }

  /**
   * By default just set the variable dont check it
   * @param string $name
   * @param string $value
   */
  public function setVar($name, $value)
  {
    $var = $this->_applicationPage->getPage()->setVar($name, $value);
    $this->_controller->getEntityManager()->persist($var);
  }

  /**
   * Validate user input
   * @param array $input
   * @return boolean
   */
  public function validateInput($input)
  {
    if ($input = $this->getForm()->processInput($input)) {
      return $input;
    }
    $this->_controller->setVar('edit', true);
    $this->_controller->addMessage('error', 'There was a problem saving your data on this page.  Please correct the errors below and retry your request.');

    return false;
  }

  /**
   * Skip an optional page
   *
   */
  public function do_skip()
  {
    if ($this->_applicant->getExternalId()) {
      $this->_controller->addMessage('error', 'You have already set your external ID, you must delete it before you can skip this page.');

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
    $answers = $this->_applicant->findAnswersByPage($this->_applicationPage->getPage());
    if (count($answers) and $answers[0]->getPageStatus() == self::SKIPPED) {
      $this->_applicant->getAnswers()->removeElement($answers[0]);
      $this->_controller->getEntityManager()->remove($answers[0]);
    }
  }
}