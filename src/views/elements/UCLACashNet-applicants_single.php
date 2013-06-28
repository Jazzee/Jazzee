<?php
/**
 * Single applicant AuthorizeNetPayment view
 */
?>
<fieldset id='answer<? print $answer->getId() ?>'>
  <legend><?php print $answer->getPayment()->getType()->getName(); ?> Payment</legend>
  <?php
  switch ($answer->getPayment()->getStatus()) {
    case \Jazzee\Entity\Payment::PENDING:?>
      <p>
        <strong>Amount:</strong>&nbsp;$<?php print $answer->getPayment()->getAmount(); ?><br />
        Status: Pending Settlement<br />
        Applicant Status Message: Approved<br />
        UCLA Reference Number: <?php print $answer->getPayment()->getVar('UCLA_REF_NO'); ?><br />
        Payment Type: <?php print $answer->getPayment()->getVar('pmttype'); ?><br />
        Transaction Number: <?php print $answer->getPayment()->getVar('tx'); ?>
      </p>
      <div class='tools'>
        <?php
        if ($this->controller->checkIsAllowed('applicants_single', 'settlePayment')) { ?>
          <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/settlePayment/' . $answer->getId()); ?>' class='actionForm'>Settle Payment</a><?php
        }
        if ($this->controller->checkIsAllowed('applicants_single', 'rejectPayment')) { ?>
          <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/rejectPayment/' . $answer->getId()); ?>' class='actionForm'>Void Payment</a><?php
        }
        if ($this->controller->checkIsAllowed('applicants_single', 'refundPayment')) { ?>
          <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/refundPayment/' . $answer->getId()); ?>' class='actionForm'>Refund Payment</a><?php
        } ?>
      </div><?php
        break;
    case \Jazzee\Entity\Payment::SETTLED:?>
      <p>
        <strong>Amount:</strong>&nbsp;$<?php print $answer->getPayment()->getAmount(); ?><br />
        Status: Marked as Settled<br />
        Applicant Status Message: Approved<br />
        UCLA Reference Number: <?php print $answer->getPayment()->getVar('UCLA_REF_NO'); ?><br />
        Payment Type: <?php print $answer->getPayment()->getVar('pmttype'); ?><br />
        Payment Type: <?php print $answer->getPayment()->getVar('pmttype'); ?><br />
        Transaction Number: <?php print $answer->getPayment()->getVar('tx'); ?>
      </p>
      <div class='tools'>
        <?php
        if ($this->controller->checkIsAllowed('applicants_single', 'rejectPayment')) { ?>
          <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/rejectPayment/' . $answer->getId()); ?>' class='actionForm'>Void Payment</a><?php
        }
        if ($this->controller->checkIsAllowed('applicants_single', 'refundPayment')) { ?>
          <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/refundPayment/' . $answer->getId()); ?>' class='actionForm'>Refund Payment</a><?php
        } ?>
      </div><?php
        break;
  case \Jazzee\Entity\Payment::REFUNDED:?>
    <p>
      <strong>Amount:</strong>&nbsp;$<?php print $answer->getPayment()->getAmount(); ?><br />
      Status: Marked as Refunded<br />
      Applicant Status Message: <?php print $answer->getPayment()->getVar('refundedReason'); ?><br />
      UCLA Reference Number: <?php print $answer->getPayment()->getVar('UCLA_REF_NO'); ?><br />
        Payment Type: <?php print $answer->getPayment()->getVar('pmttype'); ?><br />
        Transaction Number: <?php print $answer->getPayment()->getVar('tx'); ?>
    </p><?php
    break;
  case \Jazzee\Entity\Payment::REJECTED:?>
    <p>
      <strong>Amount:</strong>&nbsp;$<?php print $answer->getPayment()->getAmount(); ?><br />
      Status: Marked as Voided<br />
      Applicant Status Message: <?php print $answer->getPayment()->getVar('rejectedReason'); ?><br />
      UCLA Reference Number: <?php print $answer->getPayment()->getVar('UCLA_REF_NO'); ?><br />
        Payment Type: <?php print $answer->getPayment()->getVar('pmttype'); ?><br />
        Transaction Number: <?php print $answer->getPayment()->getVar('tx'); ?>
    </p><?php
    break;
  }
?>
</fieldset>