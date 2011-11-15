<?php 
/**
 * StandardPage Answer Element
 * @package jazzee
 * @subpackage apply
 */
  $payment = $answer->getPayment();
  if($payment->getStatus() == \Jazzee\Entity\Payment::PENDING or $payment->getStatus() == \Jazzee\Entity\Payment::SETTLED){?>
    <div class='answer active'>
    <h5><?php print $payment->getType()->getName(); ?> Payment</h5>
    <p><strong>Amount: </strong>$<?php print $payment->getAmount(); ?></p>
    <p class='status'>
    <?php 
      $class = $payment->getType()->getClass();
      switch($payment->getStatus()){
        case \Jazzee\Entity\Payment::PENDING:
          $status = $class::PENDING_TEXT;
          break;
        case \Jazzee\Entity\Payment::SETTLED:
          $status = $class::SETTLED_TEXT;
          break;
      }
    ?>
    Status: <?php print $status; ?>   
    <?php if($payment->getStatus() == \Jazzee\Entity\Payment::REJECTED){?>
      <br />Reason: <?php print $payment->getVar('rejectedReason'); ?>
    <?php } ?>
    <?php if($payment->getStatus() == \Jazzee\Entity\Payment::REFUNDED){?>
      <br />Reason: <?php print $payment->getVar('refundedReason'); ?>
    <?php } ?>
    </p>
    <p class='paymentStatusText'><?php print $answer->getPayment()->getType()->getJazzeePaymentType()->getStatusText($payment);?></p>
  <?php } else {
      $class = $payment->getType()->getClass();
      switch($payment->getStatus()){
        case \Jazzee\Entity\Payment::REJECTED:
          $reason = $payment->getVar('rejectedReason');
          $status = $class::REJECTED_TEXT;
          break;
        case \Jazzee\Entity\Payment::REFUNDED:
          $reason = $payment->getVar('refundedReason');
          $status = $class::REFUNDED_TEXT;
          break;
      }
    ?>
    <div class='answer inactive'>
    <h5><?php print $payment->getType()->getName() . ' ' . $status; ?></h5>
    <p>
      <strong>Reason:</strong> <?php print $reason; ?>
    </p>
    <p class='status'>
      <strong>Last Updated:</strong> <?php print $answer->getUpdatedAt()->format('M d Y g:i a');?>
    </p>
    <p class='paymentStatusText'><?php print $answer->getPayment()->getType()->getJazzeePaymentType()->getStatusText($payment);?></p>
  <?php } ?>
</div>