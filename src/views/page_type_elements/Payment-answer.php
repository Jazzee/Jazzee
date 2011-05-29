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
  foreach($answer->applyStatus() as $title => $value){
    print "{$title}: {$value} <br />"; 
  }
  ?>
  </p>
</div>