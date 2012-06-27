<?php
/**
 * apply_page AuthorizeNetPayment Payment type answer view
 *
 */
$payment = $answer->getPayment();
switch ($payment->getStatus()) {
  case \Jazzee\Entity\Payment::PENDING:
  case \Jazzee\Entity\Payment::SETTLED:?>
    <div class='answer active'>
      <h5><?php print $payment->getType()->getName(); ?> Payment</h5>
      <p>
        <strong>Amount: </strong>$<?php print $payment->getAmount(); ?>
        <br /><strong>Status: </strong>Approved
      </p>
      <p class='status'>
        Last Updated: <?php print $answer->getUpdatedAt()->format('M d Y g:i a'); ?></p>
    </div><?php
      break;
  case \Jazzee\Entity\Payment::REJECTED:?>
    <div class='answer inactive'>
      <h5><?php print $payment->getType()->getName(); ?> Payment</h5>
      <p>
        <strong>Amount:</strong> $<?php print $payment->getAmount(); ?>
        <br /><strong>Status:</strong> Rejected or Voided
        <br /><strong>Reason: </strong><?php print $payment->getVar('rejectedReason'); ?>
      </p>
      <p class='status'>
        Last Updated: <?php print $answer->getUpdatedAt()->format('M d Y g:i a'); ?>
      </p>
    </div><?php
      break;
  case \Jazzee\Entity\Payment::REFUNDED:?>
    <div class='answer inactive'>
      <h5><?php print $payment->getType()->getName(); ?> Payment</h5>
      <p>
        <strong>Amount:</strong> $<?php print $payment->getAmount(); ?>
        <br /><strong>Status:</strong> Refunded
        <br /><strong>Reason: </strong><?php print $payment->getVar('refundedReason'); ?>
      </p>
      <p class='status'>
        Last Updated: <?php print $answer->getUpdatedAt()->format('M d Y g:i a'); ?>
      </p>
    </div><?php
      break;
}