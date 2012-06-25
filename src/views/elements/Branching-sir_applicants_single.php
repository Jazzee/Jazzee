<?php
/**
 * Single applicant view page
 */
$page->getJazzeePage()->setApplicant($applicant);
?>
<div class='page' id='page<?php print $page->getPage()->getId() ?>'>
  <h4><?php print $page->getTitle(); ?></h4><?php
  if ($page->getJazzeePage()->getStatus() == \Jazzee\Interfaces\Page::SKIPPED) { ?>
    <p>Applicant Skipped this page.
      <?php
      if ($this->controller->checkIsAllowed('applicants_single', 'doPageAction')) {
        $answers = $page->getJazzeePage()->getAnswers();?>
        <a class='action' href='<?php print $this->path('applicants/single/' . $applicant->getId() . '/doPageAction/unskip/' . $page->getPage()->getId()) ?>'>Complete this page.</a><?php
      } ?>
    </p><?php
  } else if (count($page->getJazzeePage()->getAnswers())) { ?>
    <div class='answers'>
      <table class='answer'>
        <thead>
          <tr>
            <th><?php print $page->getPage()->getVar('branchingElementLabel') ?></th>
            <th>Answer</th>
          </tr>
        </thead>
        <tbody><?php
          foreach ($page->getJazzeePage()->getAnswers() as $answer) { ?>
            <tr id='answer<? print $answer->getId() ?>'>
              <?php $child = $answer->getChildren()->first(); ?>
              <td><?php print $child->getPage()->getTitle(); ?></td>
              <td><?php
                foreach ($child->getPage()->getElements() as $element) {
                  $element->getJazzeeElement()->setController($this->controller);
                    print '<strong>' . $element->getTitle() . ':</strong>&nbsp;' . $element->getJazzeeElement()->displayValue($child) . '<br />';
                }?>
              </td>
            </tr><?php
          } ?>
        </tbody>
      </table>
    </div><!-- answers --><?php
  } else { ?>
    <p>Applicant has not answered this section.</p><?php
  } ?>
</div> <!-- page -->