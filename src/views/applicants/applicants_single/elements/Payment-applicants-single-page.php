<?php
/**
 * Single applicant view page
 */
$page->getJazzeePage()->setApplicant($applicant);
?>
<div id='page<?php print $page->getPage()->getId() ?>'>
<h4><?php print $page->getTitle(); ?></h4>
  <div id='answers'>
    <table class='answer'>
      <thead>
        <tr>
          <th>Details</th><th>Status</th>
          <?php if($this->controller->checkIsAllowed('applicants_single', 'settlePayment') or $this->controller->checkIsAllowed('applicants_single', 'refundPayment') or $this->controller->checkIsAllowed('applicants_single', 'rejectPayment')){ ?><th>Tools</th><?php }?>
        </tr>
      </thead>
      <tbody>
      <?php 
      foreach($page->getJazzeePage()->getAnswers() as $answer){
        $elementName = \Foundation\VC\Config::findElementCacading($page->getPage()->getType()->getClass(), '', '-applicants-single-answer');
        $this->renderElement($elementName, array('page'=>$page, 'answer'=>$answer));
      }?>
      </tbody>
    </table>
 </div><!-- answers -->
<?php if($this->controller->checkIsAllowed('applicants_single', 'addAnswer')){?>
    <a class='actionForm' href='<?php print $this->path('applicants/single/' . $applicant->getId() . '/addAnswer/' . $page->getPage()->getId());?>'>Record new payment</a>
<?php }?>
</div> <!-- page -->