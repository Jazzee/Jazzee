<?php 
/**
 * applicants_single index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * Create a blank canvas to draw the applicant on
 */
//$applicant = new \Jazzee\Entity\Applicant;
?>
<div id='ajaxstatus'></div>
<div id="container">
  <div id="bio">
    <h1>
      <?php print $applicant->getFullName(); ?>
      <?php if($this->controller->checkIsAllowed('applicants_single', 'updateBio')){ ?>
        <a href="<?php print $this->path("applicants/single/{$applicant->getId()}/updateBio");?>">(edit)</a>
      <?php } ?>
    </h1>
    <h4><?php print $applicant->getEmail();?></h4>
  </div>
  <div id="status">
    <table id="statusTable">
      <thead>
        <tr>
          <?php if($this->controller->checkIsAllowed('applicants_single', 'pdf')){ ?>
            <th>Print PDF</th>
          <?php }?>
          <th>Actions</th>
          <th>Admission Status</th>
          <th>Tags</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <?php if($this->controller->checkIsAllowed('applicants_single', 'pdf')){ ?>
            <td>
              <a href="<?php print $this->path("applicants/single/{$applicant->getId()}/pdf/portrait");?>">Portrait</a><br />
              <a href="<?php print $this->path("applicants/single/{$applicant->getId()}/pdf/landscape");?>">Landscape</a>
            </td>
          <?php }?>
          <td id="actions">
            Account Created: <?php print $applicant->getCreatedAt()->format('c');?><br />
            Last Update: <?php print $applicant->getUpdatedAt()->format('c');?><br />
            Last Login: <?php print $applicant->getLastLogin()->format('c');?><br />
            Deadline Extension: 
            <?php $text = $applicant->getDeadlineExtension()?$applicant->getDeadlineExtension()->format('c'):'none'; ?>
            <?php if($this->controller->checkIsAllowed('applicants_single', 'extendDeadline')){ ?>
              <a href="<?php print $this->path("applicants/single/{$applicant->getId()}/extendDeadline");?>"><?php print $text;?></a>
            <?php } else {
              print $text;
            }?>
          </td>
          <td id="decisions">
            <?php
              $status = '';
              if($applicant->getDecision()) $status = $applicant->getDecision()->status();
              switch($status){
                case '': $status = 'No Decision'; break;
                case 'nominateAdmit': $status = 'Nominated for Admission'; break; 
                case 'nominateDeny': $status = 'Nominated for Deny'; break; 
                case 'finalDeny': $status = 'Denied'; break; 
                case 'finalAdmit': $status = 'Admited'; break; 
                case 'acceptOffer': $status = 'Accepted'; break; 
                case 'declineOffer': $status = 'Declined'; break;
              }
            ?>
            Status: <?php print $status; ?><br/>
            <?php 
              if($applicant->isLocked()){
                $actions = array(
                  array('action' => 'nominateAdmit','title'=>'Nominate for Admission','class'=>'action'),
                  array('action' => 'undoNominateAdmit','title'=>'Undo Nomination','class'=>'action'),
                  array('action' => 'nominateDeny','title'=>'Nominate for Deny','class'=>'action'),
                  array('action' => 'undoNominateDeny','title'=>'Undo Nomination','class'=>'action'),
                  array('action' => 'finalAdmit','title'=>'Admit Applicant','class'=>'actionForm'),
                  array('action' => 'undoFinalAdmit','title'=>'Undo Decision','class'=>'action'),
                  array('action' => 'finalDeny','title'=>'Deny Applicant','class'=>'actionForm'),
                  array('action' => 'undoFinalDeny','title'=>'Undo Decision','class'=>'action'),
                  array('action' => 'acceptOffer','title'=>'Accept Offer','class'=>'action'),
                  array('action' => 'declineOffer','title'=>'Decline Offer','class'=>'action'),
                  array('action' => 'undoAcceptOffer','title'=>'Undo Offer Response','class'=>'action'),
                  array('action' => 'undoDeclineOffer','title'=>'Undo Offer Response','class'=>'action')
                );
                foreach($actions as $arr){
                  if($this->controller->checkIsAllowed('applicants_single', $arr['action']) && $applicant->getDecision()->can($arr['action'])){?>
                    <a class="<?php print $arr['class']?>" href="<?php print $this->path("applicants/single/{$applicant->getId()}/{$arr['action']}");?>"><?php print $arr['title'];?></a><br />
                  <?php }
                }
                if($this->controller->checkIsAllowed('applicants_single', 'unlock')){?>
                  <a class='action' href="<?php print $this->path("applicants/single/{$applicant->getId()}/unlock");?>">Unlock Application</a>
                <?php }
              } else if($this->controller->checkIsAllowed('applicants_single', 'lock')){?>
                <a class='action' href="<?php print $this->path("applicants/single/{$applicant->getId()}/lock");?>">Lock Application</a>
              <?php }
            ?>
          </td>
          <td id="tags"></td>
        </tr>
      </tbody>
    </table>
  </div>
  <?php if($this->controller->checkIsAllowed('applicants_messages') and $threads = $applicant->getThreads()){?>
    <div id="threads" class="discussion">
      <h4>Applicant Messages</h4>
      <table>
        <thead><tr>
            <th></th>
            <th>Sent</th>
            <th>Subject</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($threads as $thread){?>
            <tr id='thread<?php print $thread->getId();?>'>
              <td class="<?php print $thread->hasUnreadMessage(\Jazzee\Entity\Message::APPLICANT)?'unread':'read';?>"></td>
              <td><?php print $thread->getCreatedAt()->format('c');?></td>
              <td><a href="<?php print $this->path("applicants/messages/single/{$thread->getId()}");?>"><?php print $thread->getSubject();?></a></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
      <a href="<?php print $this->path("applicants/messages/new/{$applicant->getId()}");?>">New Message</a>
    </div>
  <?php } ?>
  <div id='pages'>
    <?php 
    foreach($applicant->getApplication()->getApplicationPages(\Jazzee\Entity\ApplicationPage::APPLICATION) as $applicationPage){
      if($applicationPage->getJazzeePage()->showReviewPage()){
        $elementName = \Foundation\VC\Config::findElementCacading($applicationPage->getPage()->getType()->getClass(), '', '-applicants-single-page');
        $this->renderElement($elementName, array('page'=>$applicationPage,'applicant'=>$applicant));
      }
    }
    ?>
  </div>
  <div id="attachments">
    <?php foreach($applicant->getAttachments() as $attachment){
      $blob = $attachment->getAttachment();
     
      $name = $applicant->getFullName() . '_attachment_' . $attachment->getId();
      $pdf = new \Foundation\Virtual\VirtualFile($name . '.pdf', $blob, $applicant->getUpdatedAt()->format('c'));
      $png = new \Foundation\Virtual\VirtualFile($name . '.png', $this->controller->pdfThumbnail('applicant' . $applicant->getId() . 'attachment' . $attachment->getId(), $blob), $applicant->getUpdatedAt()->format('c'));
  
      $session = new \Foundation\Session();
      $store = $session->getStore('files', 900);
      $pdfStoreName = md5($name . '.pdf');
      $pngStoreName = md5($name . '.png');
      $store->$pdfStoreName = $pdf; 
      $store->$pngStoreName = $png;
      ?>
      <div id='attachment<?php print $attachment->getId();?>'>
        <a href='<?php print $this->path('file/' . \urlencode($name . '.pdf'));?>'>
          <img src='<?php print $this->path('file/' . \urlencode($name . '.png'));?>' /></a>
        <?php if($this->controller->checkIsAllowed('applicants_single', 'deleteApplicantPdf')){?>
          <a class='delete' href="<?php print $this->path("applicants/single/{$applicant->getId()}/deleteApplicantPdf/{$attachment->getId()}");?>">Delete PDF</a>
        <?php } ?>
      </div>
    <?php } ?>
    <?php if($this->controller->checkIsAllowed('applicants_single', 'attachApplicantPdf')){?>
      <a class='attach' href="<?php print $this->path("applicants/single/{$applicant->getId()}/attachApplicantPdf");?>">Attach PDF</a>
    <?php } ?>
  </div>
</div><!-- /container-->