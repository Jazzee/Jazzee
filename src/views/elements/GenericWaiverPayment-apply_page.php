<?php
/**
 * apply_page GenericWaiver Payment type answer view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @subpackage apply
 */
$payment = $answer->getPayment();
switch($payment->getStatus()){
  case \Jazzee\Entity\Payment::PENDING: ?>
  <div class='answer active'>
    <h5><?php print $payment->getType()->getName(); ?> Payment</h5>
    <p>
      <strong>Amount: </strong>$<?php print $payment->getAmount(); ?><br />
      <strong>Justification: </strong><?php print nl2br($payment->getVar('justification'));?>
      <br /><strong>Status:</strong> You have requested a fee waiver
    </p>
    <p class='status'>
      Last Updated: <?php print $answer->getUpdatedAt()->format('M d Y g:i a');?></p>
  </div>
  <?php
  break;
  case \Jazzee\Entity\Payment::SETTLED: ?>
  <div class='answer active'>
    <h5><?php print $payment->getType()->getName(); ?> Payment</h5>
    <p>
      <strong>Amount: </strong>$<?php print $payment->getAmount(); ?>
      <br /><strong>Status:</strong> Your fee waiver request was approved
    </p>
    <p class='status'>
      Last Updated: <?php print $answer->getUpdatedAt()->format('M d Y g:i a');?>
    </p>
  </div>
  <?php
  break;
  case \Jazzee\Entity\Payment::REJECTED: ?>
  <div class='answer inactive'>
    <h5><?php print $payment->getType()->getName(); ?> Payment</h5>
    <p>
      <strong>Amount:</strong> $<?php print $payment->getAmount(); ?><br />
      <strong>Justification: </strong><?php print nl2br($payment->getVar('justification'));?>
      <br /><strong>Status:</strong> Your fee waiver request was denied
      <br /><strong>Reason: </strong><?php print $payment->getVar('rejectedReason'); ?>
    </p>
    <p class='status'>
      Last Updated: <?php print $answer->getUpdatedAt()->format('M d Y g:i a');?>
    </p>
  </div>
  <?php
  break;
  case \Jazzee\Entity\Payment::REFUNDED: ?>
  <div class='answer inactive'>
    <h5><?php print $payment->getType()->getName(); ?> Payment</h5>
    <p>
      <strong>Amount:</strong> $<?php print $payment->getAmount(); ?>
      <br /><strong>Status:</strong> Your fee waiver request was withdrawn
      <br /><strong>Reason: </strong><?php print $payment->getVar('refundedReason'); ?>
    </p>
    <p class='status'>
      Last Updated: <?php print $answer->getUpdatedAt()->format('M d Y g:i a');?>
    </p>
  </div>
  <?php
  break;
}