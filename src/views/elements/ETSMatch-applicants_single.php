<?php
/**
 * Single applicant view page
 */
$page->getJazzeePage()->setApplicant($applicant);
?>
<div class='page' id='page<?php print $page->getPage()->getId() ?>'>
  <h4><?php print $page->getTitle(); ?></h4><?php
  if ($page->getJazzeePage()->getStatus() == \Jazzee\Interfaces\Page::SKIPPED) { ?>
    <p>Applicant Skipped this page.<?php
      if ($this->controller->checkIsAllowed('applicants_single', 'doPageAction')) {
        $answers = $page->getJazzeePage()->getAnswers();?>
        <a class='action' href='<?php print $this->path('applicants/single/' . $applicant->getId() . '/doPageAction/unskip/' . $page->getPage()->getId()) ?>'>Complete this page.</a><?php
      } ?>
    </p><?php
  } else if (count($page->getJazzeePage()->getAnswers())) { ?>
    <div class='answers'>
      <table class='answer'>
        <thead>
          <tr><?php
            foreach ($page->getPage()->getElements() as $element) { ?>
              <th><?php print $element->getTitle() ?></th><?php
            } ?>
            <th>Status</th>
            <th>Score</th>
            <th>Attachment</th><?php
            if ($this->controller->checkIsAllowed('applicants_single', 'editAnswer') or $this->controller->checkIsAllowed('applicants_single', 'deleteAnswer')) { ?>
              <th>Tools</th><?php
            } ?>
          </tr>
        </thead>
        <tbody><?php
          foreach ($page->getJazzeePage()->getAnswers() as $answer) { ?>
            <tr id='answer<? print $answer->getId() ?>'><?php
              foreach ($page->getPage()->getElements() as $element) {
                $element->getJazzeeElement()->setController($this->controller);?>
                <td><?php print $element->getJazzeeElement()->displayValue($answer); ?></td><?php
              } ?>
              <td>
                <strong>Last Updated:</strong> <?php print $answer->getUpdatedAt()->format('M d Y g:i a');
                if ($answer->getPublicStatus()) { ?>
                  <br />Public Status: <?php print $answer->getPublicStatus()->getName();
                }
                if ($answer->getPrivateStatus()) { ?>
                  <br />Private Status: <?php print $answer->getPrivateStatus()->getName();
                } ?>
              </td>
              <td>
                <?php
                if ($answer->getMatchedScore()) {
                  foreach ($answer->getMatchedScore()->getSummary() as $key => $value) {
                    print "<br />{$key}: {$value}";
                  }
                } else {
                  print 'This score has not been received from ETS.';
                }?>
              </td>
              <td><?php $this->renderElement('answer_attachment', array('answer' => $answer)); ?></td><?php
              if ($this->controller->checkIsAllowed('applicants_single', 'editAnswer')) { ?>
                <td><?php
                  if ($this->controller->checkIsAllowed('applicants_single', 'editAnswer')) { ?>
                    <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/editAnswer/' . $answer->getId()); ?>' class='actionForm'>Edit</a><br /><?php
                  }
                  if ($this->controller->checkIsAllowed('applicants_single', 'deleteAnswer')) { ?>
                    <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/deleteAnswer/' . $answer->getId()); ?>' class='action confirmDelete'>Delete</a><br /><?php
                  } ?>
                </td><?php
              } ?>
            </tr><?php
          } //end foreach answers ?>
        </tbody>
      </table>
    </div><!-- answers --><?php
  } else { ?>
    <p>Applicant has not answered this section.</p><?php
  } ?>
  <p class='pageTools'><?php
    if ($this->controller->checkIsAllowed('applicants_single', 'addAnswer') and $page->getJazzeePage()->getStatus() != \Jazzee\Interfaces\Page::SKIPPED and (is_null($page->getMax()) or count($page->getJazzeePage()->getAnswers()) < $page->getMax())) { ?>
      <a class='actionForm' href='<?php print $this->path('applicants/single/' . $applicant->getId() . '/addAnswer/' . $page->getPage()->getId()); ?>'>Add Answer</a>
      &nbsp;<a class='actionForm' href='<?php print $this->path('applicants/single/' . $applicant->getId() . '/pageDo/findPossibleScores/' . $page->getPage()->getId()); ?>'>Possible Scores</a>
      &nbsp;<a class='actionForm' href='<?php print $this->path('applicants/single/' . $applicant->getId() . '/pageDo/searchScores/' . $page->getPage()->getId()); ?>'>Search Scores</a><?php
    }
    if ($this->controller->checkIsAllowed('applicants_single', 'doPageAction') and !$page->isRequired() and !count($page->getJazzeePage()->getAnswers())) { ?>
      <a class='action' href='<?php print $this->path('applicants/single/' . $applicant->getId() . '/doPageAction/skip/' . $page->getPage()->getId()); ?>'>Skip Page</a><?php
    } ?>
  </p>
</div> <!-- page -->