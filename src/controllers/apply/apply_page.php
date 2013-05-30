<?php

/**
 * The actual content of the application
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplyPageController extends \Jazzee\AuthenticatedApplyController
{

  /**
   * Convience access to $this->pages[$pageId]
   * @var \Jazzee\Interfaces\Page
   */
  protected $_page;

  /**
   * Lookup applicant and make sure we are authorized to view the page
   * @see ApplyController::beforeAction()
   */
  public function beforeAction()
  {
    parent::beforeAction();
    $pageID = $this->actionParams['pageID'];
    if (!array_key_exists($pageID, $this->_pages)) {
      $this->addMessage('error', "You are not authorized to view that page.");
      $this->redirectApplyFirstPage();
    }
    if ($this->_applicant->isDeactivated() or $this->_applicant->isLocked() or ($this->_application->getClose() < new DateTime('now') and (!$this->_applicant->getDeadlineExtension() or $this->_applicant->getDeadlineExtension() < new \DateTime('now')))) {
      $this->redirectApplyPath('status');
    }
    $this->addScript($this->path('resource/scripts/controllers/apply_page.controller.js'));
    $this->_page = $this->_pages[$pageID];
    $this->setVar('page', $this->_page);
    $this->setVar('currentAnswerID', false);
  }

  /**
   * Get action page
   *
   * Where to submit forms for Pages
   * @return string
   */
  public function getActionPath()
  {
    return $this->applyPath('page/' . $this->_page->getId());
  }

  /**
   * Display the page
   */
  public function actionIndex()
  {
    if (!empty($this->post)) {
      if ($input = $this->_page->getJazzeePage()->validateInput($this->post)) {
        $this->_page->getJazzeePage()->newAnswer($input);
      }
    }
  }

  /**
   * Perform a generic ApplyPage specific action
   * Pass the input through to the apply page
   */
  public function actionDo()
  {
    $what = 'do_' . $this->actionParams['what'];
    if (method_exists($this->_page->getJazzeePage(), $what)) {
      $this->setVar('currentAnswerID', $this->actionParams['answerID']);
      $this->_page->getJazzeePage()->$what($this->actionParams['answerID'], $this->post);
    } else {
      throw new \Jazzee\Exception("Applicant {$this->_applicant->getId()} tried to call doSomething {$this->actionParams['what']} ");
    }
    $this->loadView($this->controllerName . '/index');
  }

  /**
   * Display an edit page
   * Highlight the answer being edited and fill the form with data from that answer
   */
  public function actionEdit()
  {
    $this->_page->getJazzeePage()->fill($this->actionParams['answerID']);
    $this->setVar('currentAnswerID', $this->actionParams['answerID']);
    if (!empty($this->post)) {
      if ($input = $this->_page->getJazzeePage()->validateInput($this->post)) {
        $this->_page->getJazzeePage()->updateAnswer($input, $this->actionParams['answerID']);
        $this->setVar('currentAnswerID', null);
      }
    }
    $this->loadView($this->controllerName . '/index');
  }

  /**
   * Delete an answer
   */
  public function actionDelete()
  {
    $this->_page->getJazzeePage()->deleteAnswer($this->actionParams['answerID']);
    $this->loadView($this->controllerName . '/index');
  }

  /**
   * Create the navigation from pages
   * @param array $pages
   * @return Navigation
   */
  public function getNavigation()
  {
    $navigation = new \Foundation\Navigation\Container();

    $menu = new \Foundation\Navigation\Menu();
    $navigation->addMenu($menu);

    $menu->setTitle('Application Pages');
    foreach ($this->_pages as $page) {
      $link = new \Foundation\Navigation\Link($page->getTitle());
      $link->setHref($this->applyPath('page/' . $page->getId()));
      if ($this->_page->getId() == $page->getId()) {
        $link->setCurrent(true);
      }
      switch ($page->getJazzeePage()->getStatus()) {
        case \Jazzee\Interfaces\Page::INCOMPLETE:
          $link->addClass('incomplete');
            break;
        case \Jazzee\Interfaces\Page::COMPLETE:
          $link->addClass('complete');
            break;
        case \Jazzee\Interfaces\Page::SKIPPED:
          $link->addClass('skipped');
            break;
      }
      $menu->addLink($link);
    }

    if($this->isPreviewMode()){
      $menu = new \Foundation\Navigation\Menu();
      $navigation->addMenu($menu);

      $menu->setTitle('Preview Functions');
      $link = new \Foundation\Navigation\Link('Become Administrator');
      $link->setHref($this->path('admin/login'));
      $menu->addLink($link);
      
      $link = new \Foundation\Navigation\Link('Lock Application');
      $link->setHref($this->applyPath('preview/lock'));
      $menu->addLink($link);

    }

    return $navigation;
  }

}