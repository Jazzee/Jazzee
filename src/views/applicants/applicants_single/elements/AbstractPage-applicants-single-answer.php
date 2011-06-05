<?php 
/**
 * Applicants single answer
 */
?>
<tr id='answer<?print $answer->getId() ?>'>
  <?php foreach($page->getPage()->getElements() as $element){?><td><?php print $element->getJazzeeElement()->displayValue($answer); ?></td><?php }?>
<td>
  <strong>Last Updated:</strong> <?php print $answer->getUpdatedAt()->format('M d Y g:i a');?>
    <?php if($answer->getPublicStatus()){?><br />Public Status: <?php print $answer->getPublicStatus()->getName();}?>
    <?php if($answer->getPrivateStatus()){?><br />Private Status: <?php print $answer->getPrivateStatus()->getName();}?>
</td>
<td>Attachment</td>
<?php if($this->controller->checkIsAllowed('applicants_single', 'editAnswer')){ ?>
  <td>
    <?php if($this->controller->checkIsAllowed('applicants_single', 'editAnswer')){ ?>
      <a href='<?php print $this->path('admin/applicants/single/' . $answer->getApplicant()->getId() . '/editAnswer/' . $answer->getId());?>' class='editAnswer'>Edit</a><br />     
    <?php } ?><?php if($this->controller->checkIsAllowed('applicants_single', 'deleteAnswer')){ ?>
      <a href='<?php print $this->path('admin/applicants/single/' . $answer->getApplicant()->getId() . '/deleteAnswer/' . $answer->getId());?>' class='deleteAnswer'>Delete</a><br />     
    <?php } ?>
  </td>
<?php }?>
</tr>