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
        Transaction ID: <?php print $answer->getPayment()->getVar('transactionId'); ?><br />
        Authorization Code: <?php print $answer->getPayment()->getVar('authorizationCode'); ?>
      </p>
      <div class='tools'>
        <?php
        if ($this->controller->checkIsAllowed('applicants_single', 'settlePayment')) { ?>
          <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/settlePayment/' . $answer->getId()); ?>' class='actionForm'>Refresh Settlement Status</a><?php
        }
        if ($this->controller->checkIsAllowed('applicants_single', 'rejectPayment')) { ?>
          <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/rejectPayment/' . $answer->getId()); ?>' class='actionForm'>Void Payment</a><?php
        } ?>
      </div><?php
        break;
    case \Jazzee\Entity\Payment::SETTLED:?>
      <p>
        <strong>Amount:</strong>&nbsp;$<?php print $answer->getPayment()->getAmount(); ?><br />
        Status: Settled Successfully<br />
        Applicant Status Message: Approved<br />
        Transaction ID: <?php print $answer->getPayment()->getVar('transactionId'); ?><br />
        Authorization Code: <?php print $answer->getPayment()->getVar('authorizationCode'); ?>
      </p>
      <div class='tools'>
        <?php
        $settlement = new \DateTime($answer->getPayment()->getVar('settlementTimeUTC'), new \DateTimeZone('UTC'));
        $settlement->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        $diff = $settlement->diff(new \DateTime())->days;
        if ($this->controller->checkIsAllowed('applicants_single', 'refundPayment')) {
          if ($diff < 120) { ?>
            <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/refundPayment/' . $answer->getId()); ?>' class='actionForm'>Refund Payment</a> <?php
          } else { ?>
            <p>This transaction cannot be refunded because it is <?php print $diff; ?> days old.  Authorize.net cannot refund transaction older than 120 days.</p><?php
          }
        } ?>
      </div><?php
        break;
  case \Jazzee\Entity\Payment::REFUNDED:?>
    <p>
      <strong>Amount:</strong>&nbsp;$<?php print $answer->getPayment()->getAmount(); ?><br />
      Status: Refunded<br />
      Applicant Status Message: <?php print $answer->getPayment()->getVar('refundedReason'); ?><br />
      Transaction ID: <?php print $answer->getPayment()->getVar('transactionId'); ?>
    </p><?php
      break;
  case \Jazzee\Entity\Payment::REJECTED:?>
    <p>
      <strong>Amount:</strong>&nbsp;$<?php print $answer->getPayment()->getAmount(); ?><br />
      Status: Rejected or Voided<br />
      Applicant Status Message: <?php print $answer->getPayment()->getVar('rejectedReason'); ?><br />
      Transaction ID: <?php print $answer->getPayment()->getVar('transactionId'); ?>
    </p><?php
      break;
  }
?>
</fieldset>