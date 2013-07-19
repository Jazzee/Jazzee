<?php

/**
 * The suport portal allows applicants to ask, review, and respond to questions
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @author  Lawrence Roberts <Lawrence.Roberts@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplyAccountController extends \Jazzee\AuthenticatedApplyController
{

  public function beforeAction()
  {
    parent::beforeAction();
    $restricted = array(
      'changeEmail' => 'getAllowApplicantEmailChange',
      'changePassword' => 'getAllowApplicantPasswordChange',
      'changeName' => 'getAllowApplicantNameChange',
      'printApplication' => 'getAllowApplicantPrintApplication'
    );
    if(array_key_exists($this->actionName, $restricted)){
      if(!$this->_config->$restricted[$this->actionName]()){
        $this->addMessage('error', 'You are not allowed to do that.');
        $this->redirectApplyPath('account');
      }
    }
    $layoutContentTop = '<p class="links">';
    $layoutContentTop .= '<a href="' . $this->applyPath('account') . '">My Account</a>';
    $layoutContentTop .= '<a href="' . $this->applyPath('support') . '">Support</a>';
    if ($count = $this->_applicant->unreadMessageCount()) {
      $layoutContentTop .= '<sup class="count">' . $count . '</sup>';
    }
    $layoutContentTop .= '<a href="' . $this->applyPath('applicant/logout') . '">Log Out</a></p>';

    $this->setLayoutVar('layoutContentTop', $layoutContentTop);
  }

  /**
   * Display the page
   */
  public function actionIndex()
  {
    $this->setVar('allowNameChange', $this->getConfig()->getAllowApplicantNameChange());
    $this->setVar('allowEmailChange', $this->getConfig()->getAllowApplicantEmailChange());
    $this->setVar('allowPasswordChange', $this->getConfig()->getAllowApplicantPasswordChange());
    $this->setVar('allowPrintApplication', $this->getConfig()->getAllowApplicantPrintApplication());
  }

  public function actionPrintApplication(){
    $pdf = new \Jazzee\RestrictedPDF($this->_config->getPdflibLicenseKey(), \Jazzee\ApplicantPDF::USLETTER_PORTRAIT, $this);
    $blob = $pdf->pdf($this->_applicant);
    header("Content-type: application/pdf");
    header('Content-Disposition: attachment; filename=' . $this->_applicant->getFullName() . '.pdf');
    print $blob;
    exit(0);
  }

  /**
   * Change applicant name
   */
  public function actionChangeName()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->applyPath('account/changeName'));
    $field = new \Foundation\Form\Field($form);
    $field->setLegend('Change Name');
    $form->addField($field);

    $element = $field->newElement('TextInput', 'first');
    $element->setLabel('First Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput', 'middle');
    $element->setLabel('Middle Name');

    $element = $field->newElement('TextInput', 'last');
    $element->setLabel('Last Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput', 'suffix');
    $element->setLabel('Suffix');
    $element->setFormat('Example: Jr., III');

    $element = $field->newElement('PasswordInput', 'password');
    $element->setLabel('Password');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Change Name');

    if ($input = $form->processInput($this->post)) {
      if ($this->_applicant->checkPassword($input->get('password'))) {
        $this->_applicant->setFirstName($input->get('first'));
        $this->_applicant->setMiddleName($input->get('middle'));
        $this->_applicant->setLastName($input->get('last'));
        $this->_applicant->setSuffix($input->get('suffix'));
        $this->_em->persist($this->_applicant);
        $this->addMessage('success', 'Your name was changed successfully.');
        $this->redirectApplyPath('account');
      } else {
        $form->getElementByName('password')->addMessage('Password Incorrect');
      }
    }
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Change applicant email address
   */
  public function actionChangeEmail()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->applyPath('account/changeEmail'));
    $field = new \Foundation\Form\Field($form);
    $field->setLegend('Change Email Address');
    $form->addField($field);

    $element = $field->newElement('TextInput', 'email');
    $element->setLabel('Email Address');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\EmailAddress($element));
    $element->addFilter(new \Foundation\Form\Filter\Lowercase($element));

    $element = $field->newElement('PasswordInput', 'password');
    $element->setLabel('Password');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Change Address');

    if ($input = $form->processInput($this->post)) {
      if ($this->_applicant->checkPassword($input->get('password'))) {
        $this->_applicant->setEmail($input->get('email'));
        $this->_em->persist($this->_applicant);
        $this->addMessage('success', 'Your email address was changed successfully.');
        $this->redirectApplyPath('account');
      } else {
        $form->getElementByName('password')->addMessage('Password Incorrect');
      }
    }
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Change applicant password
   */
  public function actionChangePassword()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->applyPath('account/changePassword'));
    $field = new \Foundation\Form\Field($form);
    $field->setLegend('Change Password');
    $form->addField($field);

    $element = $field->newElement('PasswordInput', 'password');
    $element->setLabel('Current Password');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('PasswordInput', 'newpassword');
    $element->setLabel('New Password');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('PasswordInput', 'confirm');
    $element->setLabel('Confirm Password');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\SameAs($element, 'newpassword'));

    $form->newButton('submit', 'Change Password');

    if ($input = $form->processInput($this->post)) {
      if ($this->_applicant->checkPassword($input->get('password'))) {
        $this->_applicant->setPassword($input->get('newpassword'));
        $this->_em->persist($this->_applicant);
        $this->addMessage('success', 'Your password was changed successfully.');
        $this->redirectApplyPath('account');
      } else {
        $form->getElementByName('password')->addMessage('Password Incorrect');
      }
    }
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Navigation
   * @return Navigation
   */
  public function getNavigation()
  {
    $navigation = new \Foundation\Navigation\Container();
    $menu = new \Foundation\Navigation\Menu();

    $menu->setTitle('Navigation');
    $link = new \Foundation\Navigation\Link('Back to Application');
    reset($this->_pages);
    $first = key($this->_pages);
    $link->setHref($this->applyPath('page/' . $first));
    $menu->addLink($link);
    $link = new \Foundation\Navigation\Link('Logout');
    $link->setHref($this->applyPath('applicant/logout'));
    $menu->addLink($link);

    $navigation->addMenu($menu);

    return $navigation;
  }

}