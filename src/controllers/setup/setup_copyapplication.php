<?php

/**
 * Copy application data from previous cycle
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class SetupCopyApplicationController extends \Jazzee\AdminController
{

  const MENU = 'Setup';
  const TITLE = 'Copy Configuration';
  const PATH = 'setup/copyapplication';
  const ACTION_INDEX = 'Copy Configuration';
  const REQUIRE_APPLICATION = false;

  /**
   * Setup the current application and cycle
   */
  public function actionIndex()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("setup/copyapplication"));
    $field = $form->newField();
    $field->setLegend('Import Application');

    $element = $field->newElement('SelectList', 'application');
    $element->setLabel('Cycle to Copy');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $applications = $this->_em->getRepository('\Jazzee\Entity\Application')->findByProgram($this->_program);

    foreach ($applications as $application) {
      $element->newItem($application->getId(), $application->getCycle()->getName());
    }
    $form->newButton('submit', 'Copy');

    if ($input = $form->processInput($this->post)) {
      $previousApplication = $this->_em->getRepository('\Jazzee\Entity\Application')->find($input->get('application'));
      $this->_application = new \Jazzee\Entity\Application();
      $this->_application->setProgram($this->_program);
      $this->_application->setCycle($this->_cycle);
      $this->_application->inVisible();
      $this->_application->unPublish();
      if($previousApplication->isByInvitationOnly()){
        $this->_application->byInvitationOnly();
      }
      $this->_application->setContactName($previousApplication->getContactName());
      $this->_application->setContactEmail($previousApplication->getContactEmail());
      $this->_application->setWelcome($previousApplication->getWelcome());
      $this->_application->setOpen($previousApplication->getOpen()->format('c'));
      $this->_application->setClose($previousApplication->getClose()->format('c'));
      $this->_application->setBegin($previousApplication->getBegin()->format('c'));
      $this->_application->setAdmitLetter($previousApplication->getAdmitletter());
      $this->_application->setDenyLetter($previousApplication->getDenyletter());
      $this->_application->setStatusIncompleteText($previousApplication->getStatusIncompleteText());
      $this->_application->setStatusNoDecisionText($previousApplication->getStatusNoDecisionText());
      $this->_application->setStatusAdmitText($previousApplication->getStatusAdmitText());
      $this->_application->setStatusDenyText($previousApplication->getStatusDenyText());
      $this->_application->setStatusAcceptText($previousApplication->getStatusAcceptText());
      $this->_application->setStatusDeclineText($previousApplication->getStatusDeclineText());
      foreach ($previousApplication->getApplicationPages() as $previousPage) {
        $page = $this->addPage($previousPage->getPage());
        $applicationPage = new \Jazzee\Entity\ApplicationPage();
        $applicationPage->setApplication($this->_application);
        $applicationPage->setPage($page);
        $applicationPage->setKind($previousPage->getKind());
        $applicationPage->setTitle($previousPage->getTitle());
        $applicationPage->setMin($previousPage->getMin());
        $applicationPage->setMax($previousPage->getMax());
        $applicationPage->setName($previousPage->getName());
        if ($previousPage->isRequired()) {
          $applicationPage->required();
        } else {
          $applicationPage->optional();
        }
        if ($previousPage->answerStatusDisplay()) {
          $applicationPage->showAnswerStatus();
        } else {
          $applicationPage->hideAnswerStatus();
        }
        $applicationPage->setInstructions($previousPage->getInstructions());
        $applicationPage->setLeadingText($previousPage->getLeadingText());
        $applicationPage->setTrailingText($previousPage->getTrailingText());
        $applicationPage->setWeight($previousPage->getWeight());
        $this->_em->persist($applicationPage);
      }
      $this->_em->persist($this->_application);
      $this->addMessage('success', 'Application copied successfully');
      unset($this->_store->AdminControllerGetNavigation);
      $this->redirectPath('setup/application');
    }

    $this->setVar('form', $form);
  }

  /**
   * Add a new page
   *
   * Do this in a deperate funciton so it can call itself
   * @param \Jazzee\Entity\Page $previousPage
   * @return \Jazzee\Entity\Page
   */
  protected function addPage(\Jazzee\Entity\Page $previousPage)
  {
    if ($previousPage->isGlobal()) {
      $page = $previousPage;
    } else {
      $page = new \Jazzee\Entity\Page();
      $page->setType($previousPage->getType());
      $page->setTitle($previousPage->getTitle());
      $page->setMin($previousPage->getMin());
      $page->setMax($previousPage->getMax());
      if ($previousPage->isRequired()) {
        $page->required();
      } else {
        $page->optional();
      }
      if ($previousPage->answerStatusDisplay()) {
        $page->showAnswerStatus();
      } else {
        $page->hideAnswerStatus();
      }
      $page->setInstructions($previousPage->getInstructions());
      $page->setLeadingText($previousPage->getLeadingText());
      $page->setTrailingText($previousPage->getTrailingText());
      $page->notGlobal();
      $this->_em->persist($page);
      foreach ($previousPage->getElements() as $previousElement) {
        $element = new \Jazzee\Entity\Element;
        $element->setType($previousElement->getType());
        $element->setFixedId($previousElement->getFixedId());
        $element->setTitle($previousElement->getTitle());
        $element->setMin($previousElement->getMin());
        $element->setMax($previousElement->getMax());
        $element->setName($previousElement->getName());
        if ($previousElement->isRequired()) {
          $element->required();
        } else {
          $element->optional();
        }
        $element->setInstructions($previousElement->getInstructions());
        $element->setFormat($previousElement->getFormat());
        $element->setWeight($previousElement->getWeight());
        $page->addElement($element);
        foreach ($previousElement->getListItems() as $previousItem) {
          $listItem = new \Jazzee\Entity\ElementListItem();
          $listItem->setValue($previousItem->getValue());
          $listItem->setWeight($previousItem->getWeight());
          $listItem->setName($previousItem->getName());
          if ($previousItem->isActive()) {
            $listItem->activate();
          } else {
            $listItem->deactivate();
          }
          $element->addItem($listItem);
          $this->_em->persist($listItem);
        }
        $this->_em->persist($element);
      }

      foreach ($previousPage->getVariables() as $previousVar) {
        $var = $page->setVar($previousVar->getName(), $previousVar->getValue());
        $this->_em->persist($var);
      }
      foreach ($previousPage->getChildren() as $previousChild) {
        $childPage = $this->addPage($previousChild);
        $page->addChild($childPage);
      }
    }

    return $page;
  }

  /**
   * Don't allow users who don't have a program and a cycle
   * Dont allow if there is already and application present
   * @param string $controller
   * @param string $action
   * @param \Jazzee\Entity\User $user
   * @param \Jazzee\Entity\Program $program
   * @param \Jazzee\Entity\Application $application
   * @return boolean
   */
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null)
  {
    if (!$program) {
      return false;
    }
    if ($application) {
      return false;
    }

    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}