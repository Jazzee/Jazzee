<?php
/**
 * apply_page Payment Page type view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
if ($page->getJazzeePage()->getStatus() == \Jazzee\Interfaces\Page::SKIPPED) { ?>
  <p class="skip">You have selected to skip this page.  You can still change your mind and <a href='<?php print $this->controller->getActionPath() . '/do/unskip'; ?>' title='complete this page'>Complete This Page</a> if you wish.</p><?php
} else {
  if (!$page->isRequired() and !count($page->getJazzeePage()->getAnswers())) {?>
    <p class="skip">This page is optional, if you do not have any information to enter you can <a href='<?php print $this->controller->getActionPath() . '/do/skip'; ?>' title='skip this page'>Skip This Page</a>.</p><?php
  } ?>
  <?php
  $completedPayment = false;
  if ($answers = $page->getJazzeePage()->getAnswers()) {
    print "<div id='answers'>";
    foreach ($answers as $answer) {
      if ($answer->getPayment()->getStatus() == \Jazzee\Entity\Payment::PENDING or $answer->getPayment()->getStatus() == \Jazzee\Entity\Payment::SETTLED) {
        $completedPayment = true;
      }
      $class = $answer->getPayment()->getType()->getClass();
      $this->renderElement($class::APPLY_PAGE_ELEMENT, array('answer' => $answer));
    }
    print '</div>';
  }
  if (!$completedPayment) {?>
    <div id='leadingText'><?php print $page->getLeadingText() ?></div>
    <?php $this->renderElement('form', array('form' => $page->getJazzeePage()->getForm())); ?>
    <div id='trailingText'><?php print $page->getTrailingText() ?></div><?php
  }
} //end else if not skipped