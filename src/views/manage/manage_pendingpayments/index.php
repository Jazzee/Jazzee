<?php 
/**
 * manage_pendingpayments index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
if($pendingPayments){ ?>
<table>
  <caption><?php print count($pendingPayments); ?> Pending Payments:</caption>
  <thead><tr><th>Payment Type</th><th>Date</th><th>Applicant</th><th>Details</th></tr></thead>
  <tbody>
  <?php foreach($pendingPayments as $payment){?>
    <tr>
      <td><?php print $payment->getType()->getName(); ?></td>
      <td><?php print $payment->getAnswer()->getUpdatedAt()->format('c'); ?></td>
      <td><?php print $payment->getAnswer()->getApplicant()->getFullName(); ?></td>
      <td><?php print $payment->getType()->getJazzeePaymentType()->getDetails($payment); ?></td>
    </tr>
  <?php } ?>
  </tbody>
</table>
<?php } else {?>
<p>There are no pending payments</p>
<?php } ?>