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
  Status: <?php print $answer->getPayment()->getType()->getJazzeePaymentType()->getStatusText($payment);?>
  <?php if($payment->getStatus() == \Jazzee\Entity\Payment::REFUNDED or $payment->getStatus() == \Jazzee\Entity\Payment::REJECTED){?>
    Reason: <?php print $payment->getVar('reasonText'); ?>
  <?php } ?>
  </p>
</div>