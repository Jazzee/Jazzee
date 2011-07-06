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
  <?php foreach($page->getPage()->getChildren()->first()->getElements() as $element){?><td><?php print $element->getJazzeeElement()->displayValue(($answer->getChildren()->first()?$answer->getChildren()->first():$answer)); ?></td><?php }?>
<td>
  <strong>Last Updated:</strong> <?php print $answer->getUpdatedAt()->format('M d Y g:i a');?><br />
  <?php if($child = $answer->getChildren()->first()){?>
      <br /><strong>Status:</strong> This recommendation was received on <?php print $child->getUpdatedAt()->format('l F jS Y g:ia');
    } else if($answer->isLocked()){?>
      <strong>Invitation Sent:</strong> <?php print $answer->getUpdatedAt()->format('l F jS Y g:ia'); ?><br />
  <?php }?>
</td>
<td>Attachment</td>
  <td>
    <?php if($this->controller->checkIsAllowed('applicants_single', 'editAnswer')){ ?>
      <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/editAnswer/' . $answer->getId());?>' class='actionForm'>Edit</a><br />
      <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/do/sendAdminInvitation/' . $answer->getId());?>' class='actionForm'>Send Invitation</a><br />
      <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/do/viewLink/' . $answer->getId());?>' class='actionForm'>View Link</a><br />
    <?php } ?><?php if($this->controller->checkIsAllowed('applicants_single', 'deleteAnswer')){ ?>
      <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/deleteAnswer/' . $answer->getId());?>' class='action'>Delete</a><br />     
    <?php } ?>
  </td>
</tr>