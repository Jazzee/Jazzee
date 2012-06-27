<?php
/**
 * apply_page CheckPayment Payment type answer view
 */
$payment = $answer->getPayment();
switch ($payment->getStatus()) {
  case \Jazzee\Entity\Payment::PENDING: ?>
    <div class='answer active'>
      <h5><?php print $payment->getType()->getName(); ?> Payment</h5>
      <p>
        <strong>Amount: </strong>$<?php print $payment->getAmount(); ?>
        <br /><strong>Status:</strong> We have not received your check
      </p>
      <?php
        $text = '<p><strong>Make Checks Payable to:</strong> ' . $payment->getType()->getVar('payable') . '</p>';
        $text .= '<p><h4>Mail Check to:</h4>' . nl2br($payment->getType()->getVar('address')) . '</p>';
        $text .= '<p><h4>Include the following information with your payment:</h4> ' . nl2br($payment->getType()->getVar('coupon')) . '</p>';
        $search = array(
          '_Applicant_Name_',
          '_Applicant_ID_',
          '_Program_Name_',
          '_Program_ID_'
        );
        $replace = array();
        $replace[] = $payment->getAnswer()->getApplicant()->getFirstName() . ' ' . $payment->getAnswer()->getApplicant()->getLastName();
        $replace[] = $payment->getAnswer()->getApplicant()->getId();
        $replace[] = $payment->getAnswer()->getApplicant()->getApplication()->getProgram()->getName();
        $replace[] = $payment->getAnswer()->getApplicant()->getApplication()->getProgram()->getId();
      ?>
      <?php print str_ireplace($search, $replace, $text); ?>
      <p class='status'>
        Last Updated: <?php print $answer->getUpdatedAt()->format('M d Y g:i a'); ?>
      </p>
    </div><?php
      break;
  case \Jazzee\Entity\Payment::SETTLED:?>
    <div class='answer active'>
      <h5><?php print $payment->getType()->getName(); ?> Payment</h5>
      <p>
        <strong>Amount: </strong>$<?php print $payment->getAmount(); ?>
        <br /><strong>Status:</strong> Your check has been received
      </p>
      <p class='status'>
        Last Updated: <?php print $answer->getUpdatedAt()->format('M d Y g:i a'); ?>
      </p>
    </div><?php
      break;
  case \Jazzee\Entity\Payment::REJECTED:?>
    <div class='answer inactive'>
      <h5><?php print $payment->getType()->getName(); ?> Payment</h5>
      <p>
        <strong>Amount:</strong> $<?php print $payment->getAmount(); ?>
        <br /><strong>Status:</strong> Your check did not clear or was rejected
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
        <br /><strong>Status:</strong> We have sent you a refund for this payment
        <br /><strong>Reason: </strong><?php print $payment->getVar('refundedReason'); ?>
      </p>
      <p class='status'>
        Last Updated: <?php print $answer->getUpdatedAt()->format('M d Y g:i a'); ?>
      </p>
    </div><?php
      break;
}