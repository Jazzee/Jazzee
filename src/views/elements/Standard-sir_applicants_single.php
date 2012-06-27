<?php
/**
 * Single applicant view page
 */
$page->getJazzeePage()->setApplicant($applicant);
?>
<div class='page' id='page<?php print $page->getPage()->getId() ?>'>
  <h4><?php print $page->getTitle(); ?></h4>
  <?php
  if ($page->getJazzeePage()->getStatus() == \Jazzee\Interfaces\Page::SKIPPED) { ?>
    <p>Applicant Skipped this page.
      <?php
      if ($this->controller->checkIsAllowed('applicants_single', 'doPageAction')) {
        $answers = $page->getJazzeePage()->getAnswers();
        ?>
        <a class='action' href='<?php print $this->path('applicants/single/' . $applicant->getId() . '/doPageAction/unskip/' . $page->getPage()->getId()) ?>'>Complete this page.</a>
      <?php
      } ?>
    </p><?php
  } else if (count($page->getJazzeePage()->getAnswers())) { ?>
    <div class='answers'>
      <table class='answer'>
        <thead>
          <tr>
            <?php
            foreach ($page->getPage()->getElements() as $element) { ?>
              <th><?php print $element->getTitle() ?></th>
            <?php
            } ?>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach ($page->getJazzeePage()->getAnswers() as $answer) { ?>
            <tr id='answer<?php print $answer->getId() ?>'>
              <?php
              foreach ($page->getPage()->getElements() as $element) {
                $element->getJazzeeElement()->setController($this->controller);
                ?><td><?php print $element->getJazzeeElement()->displayValue($answer); ?></td>
              <?php
              } ?>
            </tr>
          <?php
          } ?>
        </tbody>
      </table>
    </div><!-- answers --><?php
  } ?>
</div> <!-- page -->