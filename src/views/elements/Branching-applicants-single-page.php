<?php
/**
 * Single branching page
 */
$page->getJazzeePage()->setApplicant($applicant);
?>
<div class='page' id='page<?php print $page->getPage()->getId() ?>'>
<h4><?php print $page->getTitle(); ?></h4>
    <?php if($page->getJazzeePage()->getStatus() == \Jazzee\Page::SKIPPED){?>
      <p>Applicant Skipped this page.
      <?php if($this->controller->checkIsAllowed('applicants_single', 'doPageAction')){
        $answers = $page->getJazzeePage()->getAnswers();
        ?>
        <a class='action' href='<?php print $this->path('applicants/single/' . $applicant->getId() . '/doPageAction/unskip/' . $page->getPage()->getId())?>'>Complete this page.</a>
      <?php }?>
      </p>
    <?php } else if(count($page->getJazzeePage()->getAnswers())){ ?>
      <div class='answers'>
        <table class='answer'>
          <thead>
            <tr>
              <th><?php print $page->getPage()->getVar('branchingElementLabel')?></th>
              <th>Answer</th>
              <th>Status</th>
              <th>Attachment</th>
              <?php if($this->controller->checkIsAllowed('applicants_single', 'editAnswer') or $this->controller->checkIsAllowed('applicants_single', 'deleteAnswer')){ ?><th>Tools</th><?php }?>
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
   <?php } else { ?>
     <p>Applicant has not answered this section.</p>
    <?php } ?>
   <p class='pageTools'>
    <?php if($this->controller->checkIsAllowed('applicants_single', 'addAnswer') and $page->getJazzeePage()->getStatus() != \Jazzee\Page::SKIPPED and (is_null($page->getMax()) or count($page->getJazzeePage()->getAnswers()) < $page->getMax())){?>
      <a class='actionForm' href='<?php print $this->path('applicants/single/' . $applicant->getId() . '/addAnswer/' . $page->getPage()->getId());?>'>Add Answer</a>
    <?php }?>
    <?php if($this->controller->checkIsAllowed('applicants_single', 'doPageAction') and !$page->isRequired() and !count($page->getJazzeePage()->getAnswers())){?>
      <a class='action' href='<?php print $this->path('applicants/single/' . $applicant->getId() . '/doPageAction/skip/' . $page->getPage()->getId());?>'>Skip Page</a>
    <?php }?>
   </p>
</div> <!-- page -->