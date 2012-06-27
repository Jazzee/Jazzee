<?php
/**
 * manage_pendingpayments index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
if ($pendingPayments) {?>
  <table id ='pendingPayments'>
    <caption><?php print count($pendingPayments); ?> Pending Payments:</caption>
    <thead><tr><th>Payment Type</th><th>Date</th><th>Applicant</th><th>Program</th><th>Progress</th><th>Tags</th></tr></thead>
    <tbody><?php
      foreach ($pendingPayments as $payment) { ?>
        <tr>
          <td><?php print $payment->getType()->getName(); ?></td>
          <td><?php print $payment->getAnswer()->getUpdatedAt()->format('c'); ?></td>
          <td><span class='applicant' applicantId='<?php print $payment->getAnswer()->getApplicant()->getId(); ?>' programId='<?php print $payment->getAnswer()->getApplicant()->getApplication()->getProgram()->getId(); ?>'>
              <?php print $payment->getAnswer()->getApplicant()->getFullName(); ?> </span>
          </td>
          <td><?php print $payment->getAnswer()->getApplicant()->getApplication()->getProgram()->getName(); ?></td>
          <td><?php print $payment->getAnswer()->getApplicant()->getPercentComplete() * 100; ?>%</td>
          <?php
          $tags = array();
          foreach ($payment->getAnswer()->getApplicant()->getTags() as $tag) {
            $tags[] = $tag->getTitle();
          }
          if ($payment->getAnswer()->getApplicant()->isLocked()) {
            $tags[] = 'Locked';
          }
          if ($payment->getAnswer()->getApplicant()->hasPaid()) {
            $tags[] = 'Paid';
          }
          if ($payment->getAnswer()->getApplicant()->getDecision() and $payment->getAnswer()->getApplicant()->getDecision()->getAcceptOffer()) {
            $tags[] = 'Accepted';
          }
          if ($payment->getAnswer()->getApplicant()->getDecision() and $payment->getAnswer()->getApplicant()->getDecision()->getFinalAdmit()) {
            $tags[] = 'Admitted';
          }
          if ($payment->getAnswer()->getApplicant()->getDecision() and $payment->getAnswer()->getApplicant()->getDecision()->getDeclineOffer()) {
            $tags[] = 'Declined';
          }
          if ($payment->getAnswer()->getApplicant()->getDecision() and $payment->getAnswer()->getApplicant()->getDecision()->getFinalDeny()) {
            $tags[] = 'Denied';
          }
          asort($tags);
          $tags = implode(', ', $tags);
          ?>
          <td><?php print $tags; ?></td>
        </tr><?php
      }?>
    </tbody>
  </table><?php
} else { ?>
  <p>There are no pending payments</p><?php
}