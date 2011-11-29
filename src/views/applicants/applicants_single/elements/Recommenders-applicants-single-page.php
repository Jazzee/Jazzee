<?php
/**
 * Single applicant view page
 */
$page->getJazzeePage()->setApplicant($applicant);
?>
<div class='page' id='page<?php print $page->getPage()->getId() ?>'>
<h4><?php print $page->getTitle(); ?></h4>
  <div class='answers'>
    <table class='answer'>
      <thead>
        <tr>
          <th>Recommender</th>
          <?php foreach($page->getPage()->getChildren()->first()->getElements() as $element){?><th><?php print $element->getTitle() ?></th><?php }?>
          <th>Status</th>
          <th>Attachment</th>
          <?php if($this->controller->checkIsAllowed('applicants_single', 'editAnswer') or $this->controller->checkIsAllowed('applicants_single', 'deleteAnswer')){ ?><th>Tools</th><?php }?>
        </tr>
      </thead>
      <tbody>
      <?php 
      foreach($page->getJazzeePage()->getAnswers() as $answer){
        $this->renderElement('Recommenders-applicants-single-answer', array('page'=>$page, 'answer'=>$answer));
      }?>
      </tbody>
    </table>
 </div><!-- answers -->
<?php if($this->controller->checkIsAllowed('applicants_single', 'addAnswer')){?>
    <a class='actionForm' href='<?php print $this->path('applicants/single/' . $applicant->getId() . '/addAnswer/' . $page->getPage()->getId());?>'>Add Answer</a>
<?php }?>
</div> <!-- page -->