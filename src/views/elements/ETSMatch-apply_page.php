<?php
/**
 * apply_page Standard page type view
 *
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
        if (count($page->getjazzeePage()->getAnswers()) >= $page->getMin()) { ?>
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
  </div><?php
  if ($answers = $page->getJazzeePage()->getAnswers()) { ?>
    <div id='answers'><?php
      foreach ($answers as $answer) { ?>
        <div class='answer<?php
        if ($currentAnswerID and $currentAnswerID == $answer->getID()) {
          print ' active';
        }?>'>
          <h5>Saved Answer</h5><?php
          foreach ($answer->getPage()->getElements() as $element) {
            $element->getJazzeeElement()->setController($this->controller);
            $value = $element->getJazzeeElement()->displayValue($answer);
            if ($value) {
              print '<p><strong>' . $element->getTitle() . ':</strong>&nbsp;' . $value . '</p>';
            }
          }?>
          <p class='status'>
            Last Updated: <?php print $answer->getUpdatedAt()->format('M d Y g:i a');
            if ($answer->getPublicStatus()) { ?>
              <br />Status: <?php print $answer->getPublicStatus()->getName();
            }?>
            <br />Score Status:<?php
            if ($answer->getMatchedScore()) { ?>
              Score received for test taken on <?php print $answer->getMatchedScore()->getTestDate()->format('F jS Y') ?>.<?php
            } else { ?>
              This score has not been received from ETS.<?php
            } ?>
          </p>
          <p class='controls'><?php
            if ($currentAnswerID and $currentAnswerID == $answer->getID()) { ?>
              <a class='undo' href='<?php print $this->controller->getActionPath() ?>'>Undo</a><?php
            } else { ?>
              <a class='edit' href='<?php print $this->controller->getActionPath(); ?>/edit/<?php print $answer->getId() ?>'>Edit</a>
              <a class='delete' href='<?php print $this->controller->getActionPath(); ?>/delete/<?php print $answer->getId() ?>'>Delete</a><?php
            } ?>
          </p>
        </div><?php
      } //end foreach answers  ?>
    </div><?php
  }
  if (!empty($currentAnswerID) or is_null($page->getMax()) or count($page->getJazzeePage()->getAnswers()) < $page->getMax()) {?>
    <div id='leadingText'><?php print $page->getLeadingText() ?></div>
    <?php $this->renderElement('form', array('form' => $page->getJazzeePage()->getForm())); ?>
    <div id='trailingText'><?php print $page->getTrailingText() ?></div><?php
  }
}