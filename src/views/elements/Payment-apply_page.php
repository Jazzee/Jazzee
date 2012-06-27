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
  <div id='counter'><?php
    if ($page->getJazzeePage()->getAnswers()) {
      //infinite answers page
      if (is_null($page->getMax())) {
        if (count($page->getjazzeePage()->getAnswers()) >= $page->getMin()) {?>
          <p>You may add as many additional answers as you wish to this page, but it is not required.</p><?php
        } else { ?>
          <p>You have completed <?php print count($page->getjazzeePage()->getAnswers()) ?> of the <?php print $page->getMin() ?> required answers on this page.</p><?php
        }
      } else if ($page->getMax() > 1) {
        if ($page->getMax() - count($page->getJazzeePage()->getAnswers()) == 0) {?>
          <p>You have completed this page.</p><?php
        } else if (count($page->getjazzeePage()->getAnswers()) >= $page->getMin()) { ?>
          <p>You may complete an additional <?php print ($page->getMax() - count($page->getJazzeePage()->getAnswers())) ?> answers on this page, but it is not required.</p><?php
        } else { ?>
          <p>You have completed <?php print count($page->getjazzeePage()->getAnswers()) ?> of the <?php print $page->getMin() ?> required answers on this page.</p><?php
        }
      }
    }?>
  </div>
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
  if (!empty($currentAnswerID) or !$completedPayment or is_null($page->getMax()) or count($page->getJazzeePage()->getAnswers()) < $page->getMax()) {?>
    <div id='leadingText'><?php print $page->getLeadingText() ?></div>
    <?php $this->renderElement('form', array('form' => $page->getJazzeePage()->getForm())); ?>
    <div id='trailingText'><?php print $page->getTrailingText() ?></div><?php
  }
} //end else if not skipped