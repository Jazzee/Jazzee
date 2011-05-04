<?php 
/**
 * applicants_single single view
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
  <?php if($this->controller->checkIsAllowed('applicants_single', 'edit')): ?>
  (<a class='editApplicant' href='<?php print $this->path("applicants/single/editApplicant/{$applicant->id}")?>' title='edit applicant'>Edit</a>)
  <?php endif; ?>
  </h3>
  <?php if($this->controller->checkIsAllowed('applicants_single', 'pdf')): ?>
  <p>Print PDF 
  (<a href='<?php print $this->path("applicants/single/pdf/{$applicant->id}/portrait")?>' title='portrait pdf'>Portrait</a>)
  (<a href='<?php print $this->path("applicants/single/pdf/{$applicant->id}/landscape")?>' title='landscape pdf'>Landscape</a>)
  </p>
  <?php endif; ?>
  <table>
    <caption>Applicant Information</caption>
    <thead>
      <tr><th>Email</th><th>Actions</th><th>Admission Status</th><th>Tags</th></tr>
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
        if(!$decision AND $this->controller->checkIsAllowed('applicants_single', 'nominateAdmit'))
          $status .= "&nbsp(<a class='nominateAdmit decision' href='" . $this->path("applicants/single/nominateAdmit/{$applicant->id}") . "' title='nominate applicant for admission'>Nominate for Admission</a>)";
        if(!$decision AND $this->controller->checkIsAllowed('applicants_single', 'nominateDeny'))
          $status .= "&nbsp(<a class='nominateDeny decision' href='" . $this->path("applicants/single/nominateDeny/{$applicant->id}") . "' title='nominate applicant for deny'>Nominate for Deny</a>)";
        if($decision and !$final and $this->controller->checkIsAllowed('applicants_single', 'undoNomination'))
          $status .= "&nbsp(<a class='undoNomination decision' href='" . $this->path("applicants/single/undoNomination/{$applicant->id}") . "' title='undo nomination'>Undo Nomination</a>)";
        if($decision and !$final and $applicant->Decision->nominateAdmit and $this->controller->checkIsAllowed('applicants_single', 'finalAdmit'))
          $status .= "&nbsp(<a class='finalAdmit decision' href='" . $this->path("applicants/single/finalAdmit/{$applicant->id}") . "' title='finalize admit'>Admit Applicant</a>)";
        if($decision and !$final and $applicant->Decision->nominateDeny and $this->controller->checkIsAllowed('applicants_single', 'finalDeny'))
          $status .= "&nbsp(<a class='finalDeny decision' href='" . $this->path("applicants/single/finalDeny/{$applicant->id}") . "' title='finalize deny'>Deny Applicant</a>)";  
      } else {
        $status = 'In Progress';
      }
      ?>
        <td>
          <?php print $status ?>
          <?php  if($this->controller->checkIsAllowed('applicants_single', 'unlock') and $applicant->locked): ?>
            &nbsp;(<a class='unlock' href='<?php print $this->path("applicants/single/unlock/{$applicant->id}")?>' title='unlock applicant'>Unlock Application</a>)
          <?php endif;?>
          <?php  if($this->controller->checkIsAllowed('applicants_single', 'lock') and !$applicant->locked): ?>
            &nbsp;(<a class='lock' href='<?php print $this->path("applicants/single/lock/{$applicant->id}")?>' title='lock applicant'>Lock Application</a>)
          <?php endif;?>
          <?php  if($this->controller->checkIsAllowed('applicants_single', 'extendDeadline')): ?>
            &nbsp;(<a class='extendDeadline' href='<?php print $this->path("applicants/single/extendDeadline/{$applicant->id}")?>' title='extend deadline for applicant'>Extend Deadline</a>)
          <?php endif;?>
          <?php if($applicant->deadlineExtension and strtotime($applicant->deadlineExtension) > time())
            print '<br />Deadline Extension: ' . date('Y-m-d H:i:s', strtotime($applicant->deadlineExtension));?>
        </td>
        <td class='tags'>
          <?php foreach($applicant->Tags as $tag){
            print $tag->title . '<br />';
          }?>
          <form method='post' action='<?php print $this->path('applicants/single/addTag/'.$applicant->id)?>'>
            <input type='text' size='5' name='tag' />
            <input type='submit' value='Add' />
          </form>
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
                  $pdfName = "applicant-{$applicant->id}-answer_" . $answer->id;
                  $file = new FileContainer($answer->attachment, 'pdf', $pdfName);
                  $file->setLastModified(time());
                  $fileStore->$pdfName = $file;
                  $png = thumbnailPDF($answer->attachment, 100, 0);
                  $pngName = "applicant-{$applicant->id}-answer_" . $answer->id . "_preview";
                  $file = new FileContainer($png, 'png', $pngName);
                  $file->setLastModified(time());
                  $fileStore->$pngName = $file;
                  print "<a href='" . $this->path("file/{$pdfName}.pdf") . "'><img src='" . $this->path("file/{$pngName}.png") . "' /></a>";
                } else {
                  if($this->controller->checkIsAllowed('applicants_single', 'attachAnswerPDF')){
                    print "<a class='attachAnswerPDF' href='" . $this->path("applicants/single/attachAnswerPDF/{$answer->id}") . "'>Attach PDF</a>";
                  }
                }
              print '</td>';
              print "<td class='tools'>";
              if($this->controller->checkIsAllowed('applicants_single', 'edit')){
                foreach($answer->applicantTools() as $arr){
                  print "<a class='{$arr['class']}' href='" . $this->path("applicants/single/{$arr['path']}") . "'>{$arr['title']}</a>";
                }
              }
              print '</td>';

              print '</tr>'; 
            }
          print '</tbody></table></div>';
        else:?>
          <p>Applicant has not answered this section</p>
        <?php endif; //answers
        if($this->controller->checkIsAllowed('applicants_single', 'edit')){
          print "<a class='addAnswer' href='" . $this->path("applicants/single/addAnswer/{$applicant->id}/{$page->id}") . "'>Add Answer</a>";
        }
        ?>
    </fieldset>
  <?php endif; //showPageData?> 
  <?php endforeach; //pages?>
  <?php if($applicant->Attachments->count()):?>
  <div>
    <fieldset>
      <legend>Applicant PDFs</legend>
      <?php 
      foreach($applicant->Attachments as $attachment){
        $pdfName = "applicant-{$applicant->id}-attachment_" . $attachment->id;
        $file = new FileContainer($attachment->attachment, 'pdf', $pdfName);
        $file->setLastModified(time());
        $fileStore->$pdfName = $file;
        $png = thumbnailPDF($attachment->attachment, 100, 0);
        $pngName = "applicant-{$applicant->id}-attachment_preview_" . $attachment->id;
        $file = new FileContainer($png, 'png', $pngName);
        $file->setLastModified(time());
        $fileStore->$pngName = $file;
        print "<a href='" . $this->path("file/{$pdfName}.pdf") . "'><img src='" . $this->path("file/{$pngName}.png") . "' /></a>";
      }?>
    </fieldset>
  </div>
  <?php endif;//end if applicant has attachments ?>
  <?php
  if($this->controller->checkIsAllowed('applicants_single', 'attachApplicantPDF')){
    print "<a class='attachApplicantPDF' href='" . $this->path("applicants/single/attachApplicantPDF/{$applicant->id}/") . "'>Attach PDF to Application</a>";
  }
  ?>
</div>
<?php endif;