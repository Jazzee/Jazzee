<?php
/**
 * Single applicant view page
 */
$page->getJazzeePage()->setApplicant($applicant);
$page->getJazzeePage()->setController($this->controller);
?>
<div class='page' id='page<?php print $page->getPage()->getId(); ?>'>
  <h4><?php print $page->getTitle(); ?></h4><?php
  if ($page->getJazzeePage()->getStatus() == \Jazzee\Interfaces\Page::SKIPPED) { ?>
    <p>Applicant Skipped this page.
      <?php
      if ($this->controller->checkIsAllowed('applicants_single', 'doPageAction')) {
        $answers = $page->getJazzeePage()->getAnswers();?>
        <a class='action' href='<?php print $this->path('applicants/single/' . $applicant->getId() . '/doPageAction/unskip/' . $page->getPage()->getId()) ?>'>Complete this page.</a><?php
      } ?>
    </p><?php
  } else if (count($page->getJazzeePage()->getAnswers())) {
    //prepares the branching answers into an array so we can make a nice table
    $headers = array();
    foreach ($page->getJazzeePage()->getAnswers() as $answer) {
      $arr = array();
      $arr['Type'] = $answer->getChildren()->first()?'New School':'Known School';
      $arr['School'] = $answer->getSchool()?$answer->getSchool()->getName():null;
      $arr['Location'] = $answer->getSchool()?$answer->getSchool()->getLocationSummary():null;
      foreach ($answer->getPage()->getElements() as $element) {
        $element->getJazzeeElement()->setController($this->controller);
        $arr[$element->getTitle()] = $element->getJazzeeElement()->displayValue($answer);
      }
      if($child = $answer->getChildren()->first()){
        $values = array();
        foreach ($child->getPage()->getElements() as $element) {
          $element->getJazzeeElement()->setController($this->controller);
          $values[$element->getFixedId()] = $element->getJazzeeElement()->displayValue($child);
        }
        $arr['School'] = $values[\Jazzee\Page\Education::ELEMENT_FID_NAME];
        $parts = array(
          $values[\Jazzee\Page\Education::ELEMENT_FID_CITY],
          $values[\Jazzee\Page\Education::ELEMENT_FID_STATE],
          $values[\Jazzee\Page\Education::ELEMENT_FID_COUNTRY],
          $values[\Jazzee\Page\Education::ELEMENT_FID_POSTALCODE],
        );
        $arr['Location'] = implode(' ', $parts);
      }
      $headers = array_unique(array_merge($headers, array_keys($arr)));
      $arr['answer'] = $answer;
      $answers[] = $arr;
    }
  }
  if(!empty($answers)){?>
    <div class='answers'>
      <table class='answer'>
        <thead>
          <tr>
            <?php foreach($headers as $th){ ?>
              <th><?php print $th;?></th>
            <?php } ?>
            <th>Status</th>
            <th>Attachment</th><?php
            if ($this->controller->checkIsAllowed('applicants_single', 'editAnswer') or $this->controller->checkIsAllowed('applicants_single', 'deleteAnswer')) { ?>
              <th>Tools</th><?php
            } ?>
          </tr>
        </thead>
        <tbody><?php
          foreach ($answers as $arr) {
            $answer = $arr['answer'];?>
            <tr id='answer<? print $answer->getId(); ?>'>
              <?php foreach($headers as $key){?>
                <td><?php
                  if(array_key_exists($key, $arr)){
                    print $arr[$key];
                  }
                  ?></td>
              <?php } ?>
              <td>
                <strong>Last Updated:</strong> <?php print $answer->getUpdatedAt()->format('M d Y g:i a'); ?><?php
                if ($answer->getPublicStatus()) { ?>
                  <br />Public Status: <?php print $answer->getPublicStatus()->getName();
                }
                if ($answer->getPrivateStatus()) { ?>
                  <br />Private Status: <?php print $answer->getPrivateStatus()->getName();
                }
                if ($this->controller->checkIsAllowed('applicants_single', 'verifyAnswer')) { ?>
                  <br /><a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/verifyAnswer/' . $answer->getId()); ?>' class='actionForm'>Set Verification Status</a><?php
                } ?>
              </td>
              <td><?php $this->renderElement('answer_attachment', array('answer' => $answer)); ?></td><?php
              if ($this->controller->checkIsAllowed('applicants_single', 'editAnswer')) { ?>
                <td><?php
                  if ($this->controller->checkIsAllowed('applicants_single', 'editAnswer')) { ?>
                    <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/editAnswer/' . $answer->getId()); ?>' class='actionForm'>Edit</a><br />
                    <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/do/adminChangeSchool/' . $answer->getId()); ?>' class='actionForm'>Change School</a><br /><?php
                  }
                  if ($this->controller->checkIsAllowed('applicants_single', 'deleteAnswer')) { ?>
                    <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/deleteAnswer/' . $answer->getId()); ?>' class='action confirmDelete'>Delete</a><br /><?php
                  } ?>
                </td><?php
              } ?>
            </tr><?php
          } //end foreahc answers ?>
        </tbody>
      </table>
    </div><!-- answers --><?php
  } else { ?>
    <p>Applicant has not answered this section.</p><?php
  } ?>
  <p class='pageTools'><?php
    if ($this->controller->checkIsAllowed('applicants_single', 'addAnswer') and $page->getJazzeePage()->getStatus() != \Jazzee\Interfaces\Page::SKIPPED and (is_null($page->getMax()) or count($page->getJazzeePage()->getAnswers()) < $page->getMax())) { ?>
      <a class='actionForm' href='<?php print $this->path('applicants/single/' . $applicant->getId() . '/addAnswer/' . $page->getPage()->getId()); ?>'>Add Answer</a><?php
    }
    if ($this->controller->checkIsAllowed('applicants_single', 'doPageAction') and !$page->isRequired() and !count($page->getJazzeePage()->getAnswers())) { ?>
      <a class='action' href='<?php print $this->path('applicants/single/' . $applicant->getId() . '/doPageAction/skip/' . $page->getPage()->getId()); ?>'>Skip Page</a><?php
    } ?>
  </p>
</div> <!-- page -->