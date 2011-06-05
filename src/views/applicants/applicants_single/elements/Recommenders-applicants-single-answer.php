<?php 
/**
 * Applicants single answer
 */
?>
<tr id='answer<?print $answer->getId() ?>'>
  <td>
    <?php print $page->getPage()->getElementByFixedId(\Jazzee\Entity\Page\Recommenders::FID_FIRST_NAME)->getJazzeeElement()->displayValue($answer); ?>;
    <?php print $page->getPage()->getElementByFixedId(\Jazzee\Entity\Page\Recommenders::FID_LAST_NAME)->getJazzeeElement()->displayValue($answer); ?><br />
    <?php print $page->getPage()->getElementByFixedId(\Jazzee\Entity\Page\Recommenders::FID_INSTITUTION)->getJazzeeElement()->displayValue($answer); ?><br />
    <?php print $page->getPage()->getElementByFixedId(\Jazzee\Entity\Page\Recommenders::FID_EMAIL)->getJazzeeElement()->displayValue($answer); ?><br />
    <?php print $page->getPage()->getElementByFixedId(\Jazzee\Entity\Page\Recommenders::FID_PHONE)->getJazzeeElement()->displayValue($answer); ?><br />
  </td>
  <?php foreach($page->getPage()->getChildren()->first()->getElements() as $element){?><td><?php print $element->getJazzeeElement()->displayValue($answer); ?></td><?php }?>
<td>
  <strong>Last Updated:</strong> <?php print $answer->getUpdatedAt()->format('M d Y g:i a');?><br />
  <?php if($child = $answer->getChildren()->first()){?>
      <br /><strong>Status:</strong> This recommendation was received on <?php print $child->getLastUpdatedAt('l F jS Y g:ia');
    } else if($answer->isLocked()){?>
      <strong>Invitation Sent:</strong> <?php print $answer->getUpdatedAt()->format('l F jS Y g:ia'); ?><br />
  <?php }?>
</td>
<td>Attachment</td>
  <td>
    <?php if($this->controller->checkIsAllowed('applicants_single', 'editAnswer')){ ?>
      <a href='<?php print $this->path('admin/applicants/single/' . $answer->getApplicant()->getId() . '/editAnswer/' . $answer->getId());?>' class='editAnswer'>Edit</a><br />
      <a href='<?php print $this->path('admin/applicants/single/' . $answer->getApplicant()->getId() . '/do/sendInvitation/' . $answer->getId());?>' class='editAnswer'>Send Invitation</a><br />
      <a href='<?php print $this->path('admin/applicants/single/' . $answer->getApplicant()->getId() . '/do/viewLink/' . $answer->getId());?>' class='editAnswer'>View Link</a><br />
    <?php } ?><?php if($this->controller->checkIsAllowed('applicants_single', 'deleteAnswer')){ ?>
      <a href='<?php print $this->path('admin/applicants/single/' . $answer->getApplicant()->getId() . '/deleteAnswer/' . $answer->getId());?>' class='deleteAnswer'>Delete</a><br />     
    <?php } ?>
  </td>
</tr>