<?php 
/**
 * Applicants single answer
 */
?>
<tr id='answer<?print $answer->getId() ?>'>
  <?php foreach($page->getPage()->getElements() as $element){
    $element->getJazzeeElement()->setController($this->controller);
    ?><td><?php print $element->getJazzeeElement()->displayValue($answer); ?></td>
  <?php }?>
<td>
  <strong>Last Updated:</strong> <?php print $answer->getUpdatedAt()->format('M d Y g:i a');?>
    <?php if($answer->getPublicStatus()){?><br />Public Status: <?php print $answer->getPublicStatus()->getName();}?>
    <?php if($answer->getPrivateStatus()){?><br />Private Status: <?php print $answer->getPrivateStatus()->getName();}?>
    <?php if($this->controller->checkIsAllowed('applicants_single', 'verifyAnswer')){ ?>
      <br /><a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/verifyAnswer/' . $answer->getId());?>' class='actionForm'>Set Verification Status</a>
    <?php } ?>
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
<?php if($this->controller->checkIsAllowed('applicants_single', 'editAnswer')){ ?>
  <td>
    <?php if($this->controller->checkIsAllowed('applicants_single', 'editAnswer')){ ?>
      <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/editAnswer/' . $answer->getId());?>' class='actionForm'>Edit</a><br />     
    <?php } ?><?php if($this->controller->checkIsAllowed('applicants_single', 'deleteAnswer')){ ?>
      <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/deleteAnswer/' . $answer->getId());?>' class='action confirmDelete'>Delete</a><br />     
    <?php } ?>
  </td>
<?php }?>
</tr>