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
          <th>Recommender</th>
          <?php foreach($page->getPage()->getChildren()->first()->getElements() as $element){?><th><?php print $element->getTitle() ?></th><?php }?>
          <th>Status</th>
          <?php if($page->getJazzeePage()->allowAttachments()){?><th>Attachment</th><?php }?>
          <?php if($this->controller->checkIsAllowed('applicants_single', 'edit')){ ?><th>Tools</th><?php }?>
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
    <a class='addAnswer' href='<?php print $this->path('applicants/single/' . $applicant->getId() . '/addAnswer/' . $page->getPage()->getId());?>'>Add Answer</a>
<?php }?>
</div> <!-- page -->