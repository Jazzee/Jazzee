<?php
/**
 * Standard page LOR single applicant answer element
 */
?>
<div class='answers'>
  <table class='answer'>
    <thead>
      <tr>
        <th>Recommender</th>
        <?php
        foreach ($page->getPage()->getChildren()->first()->getElements() as $element) { ?>
          <th><?php print $element->getTitle() ?></th><?php
        } ?>
        <th>Status</th>
        <th>Attachment</th>
        <?php
        if ($this->controller->checkIsAllowed('applicants_single', 'editAnswer') or $this->controller->checkIsAllowed('applicants_single', 'deleteAnswer')) { ?>
          <th>Tools</th><?php
        } ?>
      </tr>
    </thead>
    <tbody>
      <?php
      foreach ($answers as $answer) { ?>
        <tr id='answer<?php print $answer->getId() ?>'>
          <td>
            <?php $this->renderElement('Recommenders-applicants_single-recommender', array('answer' => $answer)); ?>
          </td>
          <?php
          foreach ($page->getPage()->getChildren()->first()->getElements() as $element) {
            $element->getJazzeeElement()->setController($this->controller);?>
            <td class='answerElement element_<?php print $element->getType()->getNiceClass() ?>'><?php print $element->getJazzeeElement()->displayValue(($answer->getChildren()->first() ? $answer->getChildren()->first() : $answer)); ?></td><?php
          } ?>
          <td><?php $this->renderElement('Recommenders-applicants_single-status', array('answer' => $answer)); ?></td>
          <td><?php $this->renderElement('answer_attachment', array('answer' => $answer)); ?></td>
          <td><?php $this->renderElement('Recommenders-applicants_single-tools', array('answer' => $answer)); ?></td>
        </tr><?php
      } ?>
    </tbody>
  </table>
</div>