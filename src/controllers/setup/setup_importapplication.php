<?php

/**
 * Import application data from XML
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class SetupImportApplicationController extends \Jazzee\AdminController
{

  const MENU = 'Setup';
  const TITLE = 'Import Configuration';
  const PATH = 'setup/importapplication';
  const ACTION_INDEX = 'Import Configuration';
  const REQUIRE_APPLICATION = false;

  /**
   * Setup the current application and cycle
   */
  public function actionIndex()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("setup/importapplication"));
    $field = $form->newField();
    $field->setLegend('Import Application');

    $element = $field->newElement('FileInput', 'file');
    $element->setLabel('XML Configuration');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Blob($element));



    $form->newButton('submit', 'Import');

    if ($input = $form->processInput($this->post)) {
      $xml = simplexml_load_string($input->get('file'));
      if (!$this->_application) {
        $this->_application = new \Jazzee\Entity\Application();
        $this->_application->setProgram($this->_program);
        $this->_application->setCycle($this->_cycle);
      }
      if ($this->_application->isPublished()) {
        $this->addMessage('error', 'This application is already published.  No changes can be made.');
        $this->redirectPath('setup/importapplication');
      }
      $pages = $this->_application->getApplicationPages();
      if (count($pages)) {
        $this->addMessage('error', 'This application already has pages.  You cannot import a configuration for an application with pages.');
        $this->redirectPath('setup/importapplication');
      }
      $preferences = $xml->xpath('/response/application/preferences');
      $arr = array();
      foreach ($preferences[0]->children() as $element) {
        $arr[$element->getName()] = (string) $element;
      }
      $this->_application->setContactName($arr['contactName']);
      $this->_application->setContactEmail($arr['contactEmail']);
      $this->_application->setWelcome(html_entity_decode($arr['welcome']));
      $this->_application->setOpen($arr['open']);
      $this->_application->setClose($arr['close']);
      $this->_application->setBegin($arr['begin']);
      $this->_application->setAdmitLetter($arr['admitletter']);
      $this->_application->setDenyLetter($arr['denyletter']);
      $this->_application->setStatusIncompleteText($arr['statusIncompleteText']);
      $this->_application->setStatusNoDecisionText($arr['statusNoDecisionText']);
      $this->_application->setStatusAdmitText($arr['statusAdmitText']);
      $this->_application->setStatusDenyText($arr['statusDenyText']);
      $this->_application->setStatusAcceptText($arr['statusAcceptText']);
      $this->_application->setStatusDeclineText($arr['statusDeclineText']);
      if($arr['visible'] == '1'){
        $this->_application->visible();
      }
      if($arr['byinvitationonly'] == '1'){
        $this->_application->byInvitationOnly();
      } else {
        $this->_application->notByInvitationOnly();
      }
      if(array_key_exists('externalIdValidationExpression', $arr) and !empty($arr['externalIdValidationExpression'])){
        $this->_application->setExternalIdValidationExpression($arr['externalIdValidationExpression']);
      }
      foreach ($xml->xpath('/response/application/pages/page') as $element) {
        $attributes = $element->attributes();
        $page = $this->addPageFromXml($element);
        $applicationPage = new \Jazzee\Entity\ApplicationPage();
        $applicationPage->setApplication($this->_application);
        $applicationPage->setPage($page);
        $applicationPage->setKind((string) $attributes['kind']);
        $applicationPage->setName((string) $attributes['name']);
        $applicationPage->setTitle(html_entity_decode((string) $attributes['title']));
        $applicationPage->setMin((string) $attributes['min']);
        $applicationPage->setMax((string) $attributes['max']);
        if ((string) $attributes['required']) {
          $applicationPage->required();
        } else {
          $applicationPage->optional();
        }
        if ((string) $attributes['answerStatusDisplay']) {
          $applicationPage->showAnswerStatus();
        } else {
          $applicationPage->hideAnswerStatus();
        }
        $eattr = $element->xpath('instructions');
        $applicationPage->setInstructions((string) $eattr[0]);
        $eattr = $element->xpath('leadingText');
        $applicationPage->setLeadingText((string) $eattr[0]);
        $eattr = $element->xpath('trailingText');
        $applicationPage->setTrailingText((string) $eattr[0]);
        $applicationPage->setWeight((string) $attributes['weight']);
        $this->_em->persist($applicationPage);
      }
      $this->_em->persist($this->_application);
      $this->addMessage('success', 'Application imported successfully');
      unset($this->_store->AdminControllerGetNavigation);
    }

    $this->setVar('form', $form);
  }

  protected function addPageFromXml(SimpleXMLElement $xml)
  {
    $attributes = $xml->attributes();
    if (!empty($attributes['globalPageUuid'])) {
      $page = $this->_em->getRepository('\Jazzee\Entity\Page')->findOneBy(array('isGlobal' => true, 'uuid' => (string) $attributes['globalPageUuid']));
      if (!$page) {
        $this->addMessage('error', (string) $attributes['title'] . ' page in import references global page with uuid ' . (string) $attributes['globalPageUuid'] . ' but this page does not exist.  You need to import it before importing this application.');
        $this->_em->clear();
        $this->redirectPath('setup/importapplication');
      }
    } else {
      $page = new \Jazzee\Entity\Page();
      $page->setType($this->_em->getRepository('\Jazzee\Entity\PageType')->findOneBy(array('class' => (string) $attributes['class'])));
      $page->setTitle(html_entity_decode((string) $attributes['title']));
      $page->setMin((string) $attributes['min']);
      $page->setMax((string) $attributes['max']);
      if ((string) $attributes['required']) {
        $page->required();
      } else {
        $page->optional();
      }
      if ((string) $attributes['answerStatusDisplay']) {
        $page->showAnswerStatus();
      } else {
        $page->hideAnswerStatus();
      }
      $eattr = $xml->xpath('instructions');
      $page->setInstructions((string) $eattr[0]);
      $eattr = $xml->xpath('leadingText');
      $page->setLeadingText((string) $eattr[0]);
      $eattr = $xml->xpath('trailingText');
      $page->setTrailingText((string) $eattr[0]);
      $page->notGlobal();
      $this->_em->persist($page);
      foreach ($xml->xpath('elements/element') as $elementElement) {
        $attributes = $elementElement->attributes();
        $element = new \Jazzee\Entity\Element;
        $element->setType($this->_em->getRepository('\Jazzee\Entity\ElementType')->findOneBy(array('class' => (string) $attributes['class'])));
        if ((string) $attributes['fixedId']) {
          $element->setFixedId((string) $attributes['fixedId']);
        }
        $element->setTitle((string) $attributes['title']);
        $element->setName((string) $attributes['name']);
        $element->setMin((string) $attributes['min']);
        $element->setMax((string) $attributes['max']);
        if ((string) $attributes['required']) {
          $element->required();
        } else {
          $element->optional();
        }
        $element->setInstructions(html_entity_decode((string) $attributes['instructions']));
        $element->setFormat(html_entity_decode((string) $attributes['format']));
        $element->setWeight((string) $attributes['weight']);
        $page->addElement($element);
        foreach ($elementElement->xpath('listitems/item') as $listElement) {
          $attributes = $listElement->attributes();
          $listItem = new \Jazzee\Entity\ElementListItem();
          $listItem->setValue((string) $listElement);
          $listItem->setWeight((string) $attributes['weight']);
          $listItem->setName((string) $attributes['name']);

          if ((string) $attributes['active']) {
            $listItem->activate();
          } else {
            $listItem->deactivate();
          }
          $element->addItem($listItem);
          $this->_em->persist($listItem);
        }
        $this->_em->persist($element);
      }

      foreach ($xml->xpath('variables/variable') as $element) {
        $var = $page->setVar($element['name'], (string) $element);
        $this->_em->persist($var);
      }
      foreach ($xml->xpath('children/page') as $element) {
        $childPage = $this->addPageFromXml($element);
        $page->addChild($childPage);
      }
    }

    return $page;
  }

  /**
   * Don't allow users who don't have a program and a cycle
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

    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}