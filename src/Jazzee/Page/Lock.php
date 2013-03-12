<?php
namespace Jazzee\Page;

/**
 * Test the application for completness and lock it
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Lock implements \Jazzee\Interfaces\Page, \Jazzee\Interfaces\FormPage
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
   * The form
   * @var \Foundation\Form
   */
  protected $_form;

  /**
   * Contructor
   *
   * @param \Jazzee\Entity\ApplicationPage $applicationPage
   */
  public function __construct(\Jazzee\Entity\ApplicationPage $applicationPage)
  {
    $this->_applicationPage = $applicationPage;
  }

  /**
   *
   * @see Jazzee.Page::setController()
   */
  public function setController(\Jazzee\Controller $controller)
  {
    $this->_controller = $controller;
  }

  /**
   *
   * @see Jazzee.Page::setApplicant()
   */
  public function setApplicant(\Jazzee\Entity\Applicant $applicant)
  {
    $this->_applicant = $applicant;
  }

  public function getForm()
  {
    if (is_null($this->_form)) {
      $this->_form = new \Foundation\Form;
      $this->_form->setCSRFToken($this->_controller->getCSRFToken());
      $this->_form->setAction($this->_controller->getActionPath());
      $field = $this->_form->newField();
      $field->setLegend($this->_applicationPage->getTitle());
      $field->setInstructions($this->_applicationPage->getInstructions());

      $element = $field->newElement('RadioList', 'lock');
      $element->setLabel('Do you wish to lock your application?');
      $element->newItem(0, 'No');
      $element->newItem(1, 'Yes');

      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

      $this->_form->newButton('submit', 'Submit Application');
    }

    return $this->_form;
  }

  /**
   * Test each page to see if it is complete
   * @param FormInput $input
   * @return bool
   */
  public function validateInput($input)
  {
    if (!$input = $this->getForm()->processInput($input)) {
      return false;
    }
    if (!$input->get('lock')) {
      $this->getForm()->getElementByName('lock')->addMessage('You must answer yes to submit your application.');

      return false;
    }
    $error = false;
    foreach ($this->_applicant->getApplication()->getApplicationPages(\Jazzee\Entity\ApplicationPage::APPLICATION) as $page) {
      //dont check the lock page (this page), it will never be complete
      if ($page != $this->_applicationPage) {
        if ($page->getJazzeePage()->getStatus() == self::INCOMPLETE) {
          $error = true;
          $this->_controller->addMessage('error', 'You have not completed the ' . $page->getTitle() . ' page');
        }
      }
    }

    return !$error;
  }

  /**
   * @param type $input
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  public function newAnswer($input)
  {
    $this->_applicant->lock();
    $this->_controller->getEntityManager()->persist($this->_applicant);
    $this->_controller->addMessage('success', 'Your application has been submitted.');
    $this->_controller->redirectUrl($this->_controller->getActionPath());
  }

  /**
   * Lock Doesn't update answers
   * @param type $input
   * @param type $answerId
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   * @return boolean
   */
  public function updateAnswer($input, $answerId)
  {
    return false;
  }

  /**
   * Lock Doesn't delete answers
   * @param type $answerId
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   * @return boolean
   */
  public function deleteAnswer($answerId)
  {
    return false;
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
   * No Special setup
   * @return null
   */
  public function setupNewPage()
  {
    return;
  }

  public static function applyPageElement()
  {
    return 'Lock-apply_page';
  }

  public static function pageBuilderScriptPath()
  {
    return 'resource/scripts/page_types/JazzeePageLock.js';
  }

  /**
   * Lock Pages are always incomplete
   */
  public function getStatus()
  {
    if ($this->_applicant->isLocked()) {
      return self::COMPLETE;
    }

    return self::INCOMPLETE;
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
      'instructions' => 'Instructions',
      'leadingText' => 'Leading Text',
      'trailingText' => 'Trailing Text'
    );
    foreach($arr as $name => $niceName){
      $func = 'get' . ucfirst($name);
      if($this->_applicationPage->$func() != $applicationPage->$func()){
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

}