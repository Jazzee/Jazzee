<?php 
/**
 * applicants_view single view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
?>
<?php if(isset($applicant)):?>
<div id='single-applicant-view'>
  <h3><?php print "{$applicant->firstName} " .
      "{$applicant->middleName} " .
      "{$applicant->lastName} " .
      "{$applicant->suffix}"; ?>
  <?php if($this->controller->checkIsAllowed('applicants_view', 'edit')): ?>
  (<a class='editApplicant' href='<?php print $this->path("applicants/view/editApplicant/{$applicant->id}")?>' title='edit applicant'>Edit</a>)
  <?php endif; ?>
  </h3>
  <?php if($this->controller->checkIsAllowed('applicants_view', 'pdf')): ?>
  <p>Print PDF 
  (<a href='<?php print $this->path("applicants/view/pdf/{$applicant->id}/portrait")?>' title='portrait pdf'>Portrait</a>)
  (<a href='<?php print $this->path("applicants/view/pdf/{$applicant->id}/landscape")?>' title='landscape pdf'>Landscape</a>)
  </p>
  <?php endif; ?>
  <table>
    <caption>Applicant Information</caption>
    <thead>
      <tr><th>Email</th><th>Actions</th><th>Admission Status</th></tr>
    </thead>
    <tbody>
      <tr>
        <td><?php print $applicant->email ?></td>
        <td>
          Account Created: <?php $this->renderElement('long_date', array('date'=>$applicant->createdAt));?><br />
          Last Login: <?php $this->renderElement('long_date', array('date'=>$applicant->lastLogin));?><br />
          Last Update: <?php $this->renderElement('long_date', array('date'=>$applicant->updatedAt));?>
        </td>
      <?php
      if($applicant->locked){
        $decision = false;
        $final = false;
        if($applicant->Decision->nominateAdmit OR $applicant->Decision->nominateDeny){
          $decision = true;
        }
        if($applicant->Decision->finalAdmit OR $applicant->Decision->finalDeny){
          $final = true;
        }
        //the priority of differnt addmission status from most to least important
        $statusPriority = array(
          'declineOffer' => 'Declined offer of admission',
          'acceptOffer' => 'Accepted offer of admission',
          'decisionLetterViewed' => 'Decision letter recieved',
          'decisionLetterSent' => 'Decision letter sent',
          'finalDeny' => 'Denied (Final)',
          'finalAdmit' => 'Admitted (Final)',
          'nominateDeny' => 'Denied (Preliminary)',
          'nominateAdmit' => 'Admitted (Preliminary)',
        );
        $arr = array();
        //loop through each status and find the one with the highest priority and a timestamp
        foreach($statusPriority as $key => $value){
          if($applicant->Decision->$key){
            $arr[] = $value . ' ' . date('m/d/y', strtotime($applicant->Decision->$key));
          }
        }
        $status = implode('<br />', $arr);
        if(empty($status)) $status = 'Under Review'; //no decision has been made
        if(!$decision AND $this->controller->checkIsAllowed('applicants_view', 'nominateDecision')){
          $status .= "&nbsp(<a class='nominateAdmit decision' href='" . $this->path("applicants/view/nominateAdmit/{$applicant->id}") . "' title='nominate applicant for admission'>Nominate for Admission</a>)";
          $status .= "&nbsp(<a class='nominateDeny decision' href='" . $this->path("applicants/view/nominateDeny/{$applicant->id}") . "' title='nominate applicant for deny'>Nominate for Deny</a>)";
        }
        if($decision and !$final and $this->controller->checkIsAllowed('applicants_view', 'nominateDecision'))
          $status .= "&nbsp(<a class='undoNomination decision' href='" . $this->path("applicants/view/undoNomination/{$applicant->id}") . "' title='undo nomination'>Undo Nomination</a>)";
      } else {
        $status = 'In Progress';
      }
      ?>
        <td>
          <?php print $status ?>
          <?php  if($this->controller->checkIsAllowed('applicants_view', 'unlock') and $applicant->locked): ?>
            &nbsp(<a class='unlock' href='<?php print $this->path("applicants/view/unlock/{$applicant->id}")?>' title='unlock applicant'>Unlock Application</a>)
          <?php endif;?>
          <?php  if($this->controller->checkIsAllowed('applicants_view', 'extendDeadline')): ?>
            &nbsp(<a class='extendDeadline' href='<?php print $this->path("applicants/view/extendDeadline/{$applicant->id}")?>' title='extend deadline for applicant'>Extend Deadline</a>)
          <?php endif;?>
          <?php if($applicant->deadlineExtension and strtotime($applicant->deadlineExtension) > time())
            print '<br />Deadline Extension: ' . date('Y-m-d H:i:s', strtotime($applicant->deadlineExtension));?>
        </td>
      </tr>
    </tbody>
  </table>
  <?php foreach($pages as $page): ?>
  <?php if($page::SHOW_PAGE): ?>
    <fieldset>
      <legend><?php print $page->title ?></legend>
        <?php if($answers = $page->getAnswers()):?>
            <div id='answers'>
            <table class='answer'>
            <thead>
              <tr>
                <?php foreach($answers[0]->getElements() as $title) print "<th>{$title}</th>";?>
                <th>Status</th>
                <th>Attachment</th>
                <th>Tools</th>
              </tr>
            </thead>
            <tbody>
            <?php
            foreach($answers as $answer){
              print '<tr>';
              foreach($answer->getElements() as $id => $title){
                print '<td>' . (string)$answer->getDisplayValueForElement($id) . '</td>';
              }
              print "<td class='status'>";
                foreach($answer->applicantStatus() as $title => $value) print "{$title}: {$value} <br />";
              print '</td>';
              print "<td class='attachment'>";
                if($answer->attachment){
                  //a uniqueish name which is permanent (for caching)
                  $name = substr(sha1("answer-attachment_" . $answer->getID() . "_for_applicant_{$applicant->id}" . $answer->updatedAt),0,10);
                  $file = new FileContainer($answer->attachment, 'pdf', $name);
                  $file->setLastModified($answer->updatedAt);
                  $fileStore->$name = $file;
                  print "<a href='" . $this->path("file/{$name}.pdf") . "'>View PDF</a>";
                } else {
                  if($this->controller->checkIsAllowed('applicants_view', 'attachAnswerPDF')){
                    print "<a class='attachAnswerPDF' href='" . $this->path("applicants/view/attachAnswerPDF/{$answer->id}") . "'>Attach PDF</a>";
                  }
                }
              print '</td>';
              print "<td class='tools'>";
              if($this->controller->checkIsAllowed('applicants_view', 'edit')){
                foreach($answer->applicantTools() as $arr){
                  print "<a class='{$arr['class']}' href='" . $this->path("applicants/view/{$arr['path']}") . "'>{$arr['title']}</a>";
                }
              }
              print '</td>';

              print '</tr>'; 
            }
          print '</tbody></table></div>';
        else:?>
          <p>Applicant has not answered this section</p>
        <?php endif; //answers
        if($this->controller->checkIsAllowed('applicants_view', 'edit')){
          print "<a class='addAnswer' href='" . $this->path("applicants/view/addAnswer/{$applicant->id}/{$page->id}") . "'>Add Answer</a>";
        }
        ?>
    </fieldset>
  <?php endif; //showPageData?> 
  <?php endforeach; //pages?>
  <?php if($applicant->Attachments->count()):?>
  <div>
    <fieldset>
      <caption>Applicant PDFs</caption>
      <?php 
      foreach($applicant->Attachments as $attachment){
        //a uniqueish name which is permanent (for caching)
        $name = substr(sha1("applicant-attachment_" . $attachment->id . "_for_applicant_{$applicant->id}"),0,10);
        $file = new FileContainer($attachment->attachment, 'pdf', $name);
        $file->setLastModified(time());
        $fileStore->$name = $file;
        print "<a href='" . $this->path("file/{$name}.pdf") . "'>View Attached PDF</a> <br />";
      }?>
    </fieldset>
  </div>
  <?php endif;//end if applicant has attachments ?>
  <?php
  if($this->controller->checkIsAllowed('applicants_view', 'attachApplicantPDF')){
    print "<a class='attachApplicantPDF' href='" . $this->path("applicants/view/attachApplicantPDF/{$applicant->id}/") . "'>Attach PDF to Application</a>";
  }
  ?>
</div>
<?php endif;