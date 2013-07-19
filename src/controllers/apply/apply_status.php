<?php

/**
 * The status portal that is displayed to applicants once thier application is locked
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplyStatusController extends \Jazzee\AuthenticatedApplyController
{

  /**
   * Status array
   * @var array
   */
  protected $status;

  public function beforeAction()
  {
    parent::beforeAction();
    //if the applicant hasn't locked and the application isn't closed
    if (
            !($this->_applicant->isLocked() or $this->_applicant->isDeactivated()) AND
            ($this->_application->getClose() > new DateTime('now') or ($this->_applicant->getDeadlineExtension() and $this->_applicant->getDeadlineExtension() > new \DateTime('now')))
    ) {
      $this->addMessage('notice', "You have not completed your application.");
      $this->redirectApplyFirstPage();
    }
  }

  /**
   * Display the page
   */
  public function actionIndex()
  {
    if ($this->_applicant->isDeactivated()) {
      $statusPageText = $this->_application->getStatusDeactivatedText();
    } else if (!$this->_applicant->isLocked()) {
      $statusPageText = $this->_application->getStatusIncompleteText();
    } else {
      switch ($this->_applicant->getDecision()->status()) {
        case 'finalDeny':
          $statusPageText = $this->_application->getStatusDenyText();
            break;
        case 'finalAdmit':
          $statusPageText = $this->_application->getStatusAdmitText();
            break;
        case 'acceptOffer':
          $statusPageText = $this->_application->getStatusAcceptText();
            break;
        case 'declineOffer':
          $statusPageText = $this->_application->getStatusDeclineText();
            break;
        default:
          $statusPageText = $this->_application->getStatusNoDecisionText();
      }
    }
    $search = array(
      '_Applicant_Name_',
      '_Application_Deadline_',
      '_SIR_Link_',
      '_Admit_Letter_',
      '_Deny_Letter_',
      '_Offer_Response_Deadline_',
      '_Admit_Date_',
      '_Deny_Date_',
      '_Accept_Date_',
      '_Decline_Date_'
    );
    $replace = array(
      $this->_applicant->getFullName(),
      $this->_applicant->getDeadline()?$this->_applicant->getDeadline()->format('l F jS Y g:ia'):'',
      $this->applyPath('status/sir'),
      $this->applyPath('status/admitLetter'),
      $this->applyPath('status/denyLetter')
    );
    if ($this->_applicant->getDecision()) {

      if (!$this->_applicant->getDecision()->getDecisionViewed() and ($this->_applicant->getDecision()->getFinalAdmit() or $this->_applicant->getDecision()->getFinalDeny())) {
        $this->_applicant->getDecision()->decisionViewed();
        $this->_em->persist($this->_applicant);
      }
      $replace[] = ($this->_applicant->getDecision()->getOfferResponseDeadline()) ? $this->_applicant->getDecision()->getOfferResponseDeadline()->format('l F jS Y g:ia') : null;
      $replace[] = ($this->_applicant->getDecision()->getFinalAdmit()) ? $this->_applicant->getDecision()->getFinalAdmit()->format('l F jS Y g:ia') : null;
      $replace[] = ($this->_applicant->getDecision()->getFinalDeny()) ? $this->_applicant->getDecision()->getFinalDeny()->format('l F jS Y g:ia') : null;
      $replace[] = ($this->_applicant->getDecision()->getAcceptOffer()) ? $this->_applicant->getDecision()->getAcceptOffer()->format('l F jS Y g:ia') : null;
      $replace[] = ($this->_applicant->getDecision()->getDeclineOffer()) ? $this->_applicant->getDecision()->getDeclineOffer()->format('l F jS Y g:ia') : null;
    } else {
      $replace[] = null;
      $replace[] = null;
      $replace[] = null;
      $replace[] = null;
      $replace[] = null;
    }
    $statusPageText = str_ireplace($search, $replace, $statusPageText);
    $this->setVar('statusPageText', nl2br($statusPageText));
    $this->setVar('pages', $this->_pages);
  }

  /**
   * SIR Form
   */
  public function actionSir()
  {
    if ($this->_applicant->getDecision()->status() != 'finalAdmit') {
      throw new \Jazzee\Exception("Applicant #{$this->_applicant->getId()} tried to access SIR but is not in the status finalAdmit", E_USER_NOTICE, 'You do not have access to this page.');
    }
    $sirAcceptPage = false;
    if ($pages = $this->_application->getApplicationPages(\Jazzee\Entity\ApplicationPage::SIR_ACCEPT)) {
      $sirAcceptPage = $pages[0];
      $sirAcceptPage->getJazzeePage()->setApplicant($this->_applicant);
      $sirAcceptPage->getJazzeePage()->setController($this);
    }
    $sirDeclinePage = false;
    if ($pages = $this->_application->getApplicationPages(\Jazzee\Entity\ApplicationPage::SIR_DECLINE)) {
      $sirDeclinePage = $pages[0];
      $sirDeclinePage->getJazzeePage()->setApplicant($this->_applicant);
      $sirDeclinePage->getJazzeePage()->setController($this);
    }
    $this->setVar('sirAcceptPage', $sirAcceptPage);
    $this->setVar('sirDeclinePage', $sirDeclinePage);
    $this->setVar('actionPath', $this->applyPath('status/sir'));

    $form = new \Foundation\Form();
    $form->setAction($this->applyPath('status/sir'));
    $field = $form->newField();
    $field->setLegend('Confirm Enrolment');
    $field->setInstructions('You must confirm your enrollment by <strong><em>' . $this->_applicant->getDecision()->getOfferResponseDeadline()->format('l F jS Y g:ia') . '</em></strong>. If you do not confirm your enrollment your space may be released to another applicant.');
    $element = $field->newElement('RadioList', 'confirm');
    $element->setLabel('Do you intend to register for the term in which you applied?');
    $element->newItem(0, 'No');
    $element->newItem(1, 'Yes');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $buttonText = ($sirAcceptPage or $sirDeclinePage) ? 'Next' : 'Save';
    $form->newButton('submit', $buttonText);
    $form->newHiddenElement('sirLevel', '1');
    $this->setVar('form', $form);
    if ($this->post) {
      if (isset($this->post['sirLevel']) and $this->post['sirLevel'] == 1 and $input = $form->processInput($this->post)) {
        $this->setVar('confirm', $input->get('confirm'));
        if ($input->get('confirm') == 1 and !$sirAcceptPage) {
          $this->acceptApplicant();
        } else if ($input->get('confirm') == 0 and !$sirDeclinePage) {
          $this->declineApplicant();
        }
      } else if (isset($this->post['confirm'])) {
        if ($this->post['confirm'] == 0) {
          $this->setVar('confirm', 0);
          if ($input = $sirDeclinePage->getJazzeePage()->validateInput($this->post)) {
            $sirDeclinePage->getJazzeePage()->newAnswer($input);
            $this->declineApplicant();
          }
        }
        if ($this->post['confirm'] == 1) {
          $this->setVar('confirm', 1);
          if ($input = $sirAcceptPage->getJazzeePage()->validateInput($this->post)) {
            $sirAcceptPage->getJazzeePage()->newAnswer($input);
            $this->acceptApplicant();
          }
        }
      }
    }
  }

  /**
   * Accept applicant
   * Break out the steps to accept an applicant so they dont get repeated all over
   */
  protected function acceptApplicant()
  {
    $this->_applicant->getDecision()->acceptOffer();
    $this->_em->persist($this->_applicant);
    $this->addMessage('success', 'Your intent to enroll was recorded.');
    $this->redirectApplyPath('status');
  }

  /**
   * Decline applicant
   * Break out the steps to decline an applicant so they dont get repeated all over
   */
  protected function declineApplicant()
  {
    $this->_applicant->getDecision()->declineOffer();
    $this->_em->persist($this->_applicant);
    $this->addMessage('success', 'Your decision not to enroll was recorded.');
    $this->redirectApplyPath('status');
  }

  /**
   * Admit Letter
   */
  public function actionAdmitLetter()
  {
    if (!$this->_applicant->getDecision()->getFinalAdmit()) {
      throw new \Jazzee\Exception("Applicant {$this->_applicant->getFullName()} tried to access final admit letter, but they are not admitted.", E_USER_ERROR, 'You do not have access to that page.');
    }
    $text = $this->_application->getAdmitLetter();
    $search = array(
      '_Admit_Date_',
      '_Applicant_Name_',
      '_Offer_Response_Deadline_'
    );
    $replace = array();
    $replace[] = $this->_applicant->getDecision()->getFinalAdmit()->format('F jS Y');
    $replace[] = $this->_applicant->getFullName();
    $replace[] = $this->_applicant->getDecision()->getOfferResponseDeadline()->format('F jS Y g:ia');
    $text = str_ireplace($search, $replace, $text);

    $text = nl2br($text);
    $this->setVar('text', $text);
  }

  /**
   * Deny Letter
   */
  public function actionDenyLetter()
  {
    if (!$this->_applicant->getDecision()->getFinalDeny()) {
      throw new \Jazzee\Exception("Applicant {$this->_applicant->getFullName()} tried to access final admit letter, but they are not admitted.", E_USER_ERROR, 'You do not have access to that page.');
    }
    $text = $this->_application->getDenyLetter();
    $search = array(
      '_Deny_Date_',
      '_Applicant_Name_'
    );
    $replace = array();
    $replace[] = $this->_applicant->getDecision()->getFinalDeny()->format('F jS Y');
    $replace[] = $this->_applicant->getFullName();
    $text = str_ireplace($search, $replace, $text);

    $text = nl2br($text);
    $this->setVar('text', $text);
  }

  /**
   * Get the path to this page
   * @return string
   */
  public function getActionPath()
  {
    return $this->applyPath('status');
  }

  /**
   * Perform a generic ApplyPage specific action
   * Pass the input through to the apply page
   */
  public function actionDo()
  {
    $what = 'do_' . $this->actionParams['what'];
    $applicationPage = $this->_pages[$this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page' => $this->actionParams['pageId'], 'application' => $this->_application->getId()))->getId()];
    if (method_exists($applicationPage->getJazzeePage(), $what)) {
      $applicationPage->getJazzeePage()->$what($this->actionParams['answerId'], $this->post);
    }
    $this->redirectApplyPath('status');
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

    $link = new \Foundation\Navigation\Link('Your Status');
    $link->setHref($this->applyPath('status'));
    $menu->addLink($link);
    if ($this->_applicant->getDecision() and $this->_applicant->getDecision()->status() == 'finalAdmit') {
      $link = new \Foundation\Navigation\Link('Confirm Enrollment');
      $link->setHref($this->applyPath('status/sir'));
      $menu->addLink($link);
    }
    if ($this->_applicant->getDecision() and $this->_applicant->getDecision()->getFinalAdmit()) {
      $link = new \Foundation\Navigation\Link('View Decision Letter');
      $link->setHref($this->applyPath('status/admitLetter'));
      $menu->addLink($link);
    }
    if ($this->_applicant->getDecision() and $this->_applicant->getDecision()->getFinalDeny()) {
      $link = new \Foundation\Navigation\Link('View Decision Letter');
      $link->setHref($this->applyPath('status/denyLetter'));
      $menu->addLink($link);
    }

    $navigation->addMenu($menu);

    if($this->_config->getAllowApplicantPrintApplication()){
      $actions = new \Foundation\Navigation\Menu();
      $actions->setTitle('Actions');
      $print = new \Foundation\Navigation\Link('Print Application');
      $print->setHref($this->applyPath('account/printApplication'));
      $actions->addLink($print);

      $navigation->addMenu($actions);
    }
    if($this->isPreviewMode()){
      $menu = new \Foundation\Navigation\Menu();
      $navigation->addMenu($menu);

      $menu->setTitle('Preview Functions');
      $link = new \Foundation\Navigation\Link('Unlock Application');
      $link->setHref($this->applyPath('preview/unlock'));
      $menu->addLink($link);

    }

    return $navigation;
  }

}