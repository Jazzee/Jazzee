<?php 
/**
 * Applicants single answer
 */
?>

<tr id='answer<?php print $answer->getId() ?>'>
  <td>
    <?php print $page->getPage()->getElementByFixedId(\Jazzee\Entity\Page\Recommenders::FID_FIRST_NAME)->getJazzeeElement()->displayValue($answer); ?>;
    <?php print $page->getPage()->getElementByFixedId(\Jazzee\Entity\Page\Recommenders::FID_LAST_NAME)->getJazzeeElement()->displayValue($answer); ?><br />
    <?php print $page->getPage()->getElementByFixedId(\Jazzee\Entity\Page\Recommenders::FID_INSTITUTION)->getJazzeeElement()->displayValue($answer); ?><br />
    <?php print $page->getPage()->getElementByFixedId(\Jazzee\Entity\Page\Recommenders::FID_EMAIL)->getJazzeeElement()->displayValue($answer); ?><br />
    <?php print $page->getPage()->getElementByFixedId(\Jazzee\Entity\Page\Recommenders::FID_PHONE)->getJazzeeElement()->displayValue($answer); ?><br />
  </td>
  <?php foreach($page->getPage()->getChildren()->first()->getElements() as $element){
    $element->getJazzeeElement()->setController($this->controller);?>
    <td><?php print $element->getJazzeeElement()->displayValue(($answer->getChildren()->first()?$answer->getChildren()->first():$answer)); ?></td>
  <?php }?>
<td>
  <strong>Last Updated:</strong> <?php print $answer->getUpdatedAt()->format('M d Y g:i a');?><br />
  <?php if($child = $answer->getChildren()->first()){?>
      <br /><strong>Status:</strong> This recommendation was received on <?php print $child->getUpdatedAt()->format('l F jS Y g:ia');
    } else if($answer->isLocked()){?>
      <strong>Invitation Sent:</strong> <?php print $answer->getUpdatedAt()->format('l F jS Y g:ia'); ?><br />
  <?php }?>
</td>
<td>
<?php if($attachment = $answer->getAttachment()){
    $pdfName = $answer->getPage()->getTitle() . '_attachment_' . $answer->getId() . '.pdf';
    $pngName = $answer->getPage()->getTitle() . '_attachment_' . $answer->getId() . 'preview.png';
    if(!$pdfFile = $this->controller->getStoredFile($pdfName) or $pdfFile->getLastModified() < $answer->getUpdatedAt()){
      $this->controller->storeFile($pdfName, $attachment->getAttachment());
    }
    if(!$pngFile = $this->controller->getStoredFile($pngName) or $pngFile->getLastModified() < $answer->getUpdatedAt()){
      $this->controller->storeFile($pngName, $attachment->getThumbnail());
    }
  ?>
    <a href="<?php print $this->path('file/' . \urlencode($pdfName));?>"><img src="<?php print $this->path('file/' . \urlencode($pngName));?>" /></a>
    <?php if($this->controller->checkIsAllowed('applicants_single', 'deleteAnswerPdf')){ ?>
      <br /><a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/deleteAnswerPdf/' . $answer->getId());?>' class='action'>Delete PDF</a>
    <?php } ?>
<?php } else if($this->controller->checkIsAllowed('applicants_single', 'attachAnswerPdf')){ ?>
  <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/attachAnswerPdf/' . $answer->getId());?>' class='actionForm'>Attach PDF</a>
<?php } ?>
</td>
  <td>
    <?php if($this->controller->checkIsAllowed('applicants_single', 'editAnswer')){ ?>
      <?php if($answer->getChildren()->first()){?>
        <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/do/editLor/' . $answer->getId());?>' class='actionForm'>Edit Recommendation</a><br />
      <?php } else { ?>
        <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/editAnswer/' . $answer->getId());?>' class='actionForm'>Edit Recommender Information</a><br />
        <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/do/sendAdminInvitation/' . $answer->getId());?>' class='actionForm'>Send Invitation</a><br />
        <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/do/completeLor/' . $answer->getId());?>' class='actionForm'>Complete Recommendation</a><br />
        <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/do/viewLink/' . $answer->getId());?>' class='actionForm'>View Link</a><br />
      <?php } ?>
      <?php } ?><?php if($this->controller->checkIsAllowed('applicants_single', 'deleteAnswer')){ ?>
        <?php if($answer->getChildren()->first()){?>
          <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/doAction/deleteLor/' . $answer->getId());?>' class='action confirmDelete'>Delete Recommendation</a><br />
        <?php } else { ?>
          <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/deleteAnswer/' . $answer->getId());?>' class='action confirmDelete'>Delete</a><br />  
        <?php } ?>
    <?php } ?>
  </td>
</tr>