<?php
/**
 * Single applicant view page
 */
$page->getJazzeePage()->setApplicant($applicant);
?>
<div class='page' id='page<?php print $page->getPage()->getId() ?>'>
  <h4><?php print $page->getTitle(); ?></h4>
  <div class='answers'>
    <?php
    foreach ($page->getJazzeePage()->getAnswers() as $answer) {
      $class = $answer->getPayment()->getType()->getClass();
      $this->renderElement($class::APPLICANTS_SINGLE_ELEMENT, array('answer' => $answer));
    }?>
  </div><!-- answers --><?php
  if ($this->controller->checkIsAllowed('applicants_single', 'addAnswer')) { ?>
    <a class='actionForm' href='<?php print $this->path('applicants/single/' . $applicant->getId() . '/addAnswer/' . $page->getPage()->getId()); ?>'>Record new payment</a><?php
  } ?>
</div> <!-- page -->