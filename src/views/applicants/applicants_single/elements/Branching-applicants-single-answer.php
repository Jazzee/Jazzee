<?php 
/**
 * Applicants single answer
 */
?>
<tr id='answer<?print $answer->getId() ?>'>
  <?php $child = $answer->getChildren()->first();?>
  <td><?php print $child->getPage()->getTitle(); ?></td>
  <td><?php foreach($child->getPage()->getElements() as $element){
      print '<strong>' . $element->getTitle() . ':</strong>&nbsp;' . $element->getJazzeeElement()->displayValue($child) . '<br />'; 
  }?></td>
<td>
  <strong>Last Updated:</strong> <?php print $answer->getUpdatedAt()->format('M d Y g:i a');?>
    <?php if($answer->getPublicStatus()){?><br />Public Status: <?php print $answer->getPublicStatus()->getName();}?>
    <?php if($answer->getPrivateStatus()){?><br />Private Status: <?php print $answer->getPrivateStatus()->getName();}?>
</td>
<td>
<?php if($attachment = $answer->getAttachment()){
    $blob = $attachment->getAttachment();
    $name = $answer->getPage()->getTitle() . '_attachment_' . $answer->getId();
    $pdf = new \Foundation\Virtual\VirtualFile($name . '.pdf', $blob, $answer->getUpdatedAt()->format('c'));
    $png = new \Foundation\Virtual\VirtualFile($name . '.png', $this->controller->pdfThumbnail('applicant' . $answer->getApplicant()->getId() . 'answer' . $answer->getId() . 'attachment' . $attachment->getId(), $blob), $answer->getUpdatedAt()->format('c'));
  
    $session = new \Foundation\Session();
    $store = $session->getStore('files', 900);
    $pdfStoreName = md5($name . '.pdf');
    $pngStoreName = md5($name . '.png');
    $store->$pdfStoreName = $pdf; 
    $store->$pngStoreName = $png;
    ?>
    <a href="<?php print $this->path('file/' . \urlencode($name . '.pdf'));?>"><img src="<?php print $this->path('file/' . \urlencode($name . '.png'));?>" /></a>
<?php } else if($this->controller->checkIsAllowed('applicants_single', 'attachAnswerPdf')){ ?>
  <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/attachAnswerPdf/' . $answer->getId());?>' class='actionForm'>Attach PDF</a>
<?php } ?>
</td>
<?php if($this->controller->checkIsAllowed('applicants_single', 'editAnswer')){ ?>
  <td>
    <?php if($this->controller->checkIsAllowed('applicants_single', 'editAnswer')){ ?>
      <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/editAnswer/' . $answer->getId());?>' class='actionForm'>Edit</a><br />     
    <?php } ?><?php if($this->controller->checkIsAllowed('applicants_single', 'deleteAnswer')){ ?>
      <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/deleteAnswer/' . $answer->getId());?>' class='action'>Delete</a><br />     
    <?php } ?>
  </td>
<?php }?>
</tr>