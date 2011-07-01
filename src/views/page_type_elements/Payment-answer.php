<?php 
/**
 * StandardPage Answer Element
 * @package jazzee
 * @subpackage apply
 */
?>
<div class='answer'>
  <h5>Payment</h5>
  <?php 
  $payment = $answer->getPayment();
  print '<p><strong>Type:</strong>&nbsp;' . $payment->getType()->getName() . '</p>';
  print '<p><strong>Amount:</strong>&nbsp;$' . $payment->getAmount() . '</p>';
  ?>
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
      case \Jazzee\Entity\Payment::REJECTED:
        $status = $class::REJECTED_TEXT;
        break;
      case \Jazzee\Entity\Payment::REFUNDED:
        $status = $class::REFUNDED_TEXT;
        break;
    }
  ?>
  Status: <?php print $status; ?>   
  <?php if($payment->getStatus() == \Jazzee\Entity\Payment::REFUNDED or $payment->getStatus() == \Jazzee\Entity\Payment::REJECTED){?>
    Reason: <?php print $payment->getVar('reasonText'); ?>
  <?php } ?>
  </p>
  <p class='paymentStatusText'><?php print $answer->getPayment()->getType()->getJazzeePaymentType()->getStatusText($payment);?></p>
</div>