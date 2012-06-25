<?php
/**
 * Single applicant Check payment view
 */
?>
<fieldset id='answer<? print $answer->getId() ?>'>
  <legend><?php print $answer->getPayment()->getType()->getName(); ?> Payment</legend><?php
  switch ($answer->getPayment()->getStatus()) {
    case \Jazzee\Entity\Payment::PENDING:?>
      <p>
        <strong>Amount:</strong>&nbsp;$<?php print $answer->getPayment()->getAmount(); ?><br />
        Status: No check received<br />
      </p>
      <h5>Directions to applicant</h5>
      <?php
      $text = '<p><strong>Make Checks Payable to:</strong> ' . $answer->getPayment()->getType()->getVar('payable') . '</p>';
      $text .= '<p><h4>Mail Check to:</h4>' . nl2br($answer->getPayment()->getType()->getVar('address')) . '</p>';
      $text .= '<p><h4>Include the following information with your payment:</h4> ' . nl2br($answer->getPayment()->getType()->getVar('coupon')) . '</p>';
      $search = array(
        '_Applicant_Name_',
        '_Applicant_ID_',
        '_Program_Name_',
        '_Program_ID_'
      );
      $replace = array();
      $replace[] = $answer->getApplicant()->getFirstName() . ' ' . $answer->getApplicant()->getLastName();
      $replace[] = $answer->getApplicant()->getId();
      $replace[] = $answer->getApplicant()->getApplication()->getProgram()->getName();
      $replace[] = $answer->getApplicant()->getApplication()->getProgram()->getId();
      ?>
      <?php print str_ireplace($search, $replace, $text); ?>
      <div class='tools'><?php
        if ($this->controller->checkIsAllowed('applicants_single', 'settlePayment')) { ?>
          <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/settlePayment/' . $answer->getId()); ?>' class='actionForm'>Clear Check</a><?php
        }
        if ($this->controller->checkIsAllowed('applicants_single', 'rejectPayment')) { ?>
          <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/rejectPayment/' . $answer->getId()); ?>' class='actionForm'>Void Payment</a><?php
        } ?>
      </div><?php
        break;
    case \Jazzee\Entity\Payment::SETTLED:?>
      <p>
        <strong>Amount:</strong>&nbsp;$<?php print $answer->getPayment()->getAmount(); ?><br />
        Status: Check received and payment settled<br />
        Check Number: <?php print $answer->getPayment()->getVar('checkNumber'); ?><br />
        Settlement Date: <?php print $answer->getPayment()->getVar('checkSettlementDate'); ?>
      </p>
      <div class='tools'><?php
      if ($this->controller->checkIsAllowed('applicants_single', 'refundPayment')) { ?>
          <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/refundPayment/' . $answer->getId()); ?>' class='actionForm'>Record a refund payment</a><?php
      } ?>
      </div><?php
        break;
  case \Jazzee\Entity\Payment::REFUNDED:?>
      <p>
        <strong>Amount:</strong>&nbsp;$<?php print $answer->getPayment()->getAmount(); ?><br />
        Status: Refunded<br />
        Applicant Status Message: <?php print $answer->getPayment()->getVar('refundedReason'); ?><br />
      </p><?php
      break;
  case \Jazzee\Entity\Payment::REJECTED:?>
      <p>
        <strong>Amount:</strong>&nbsp;$<?php print $answer->getPayment()->getAmount(); ?><br />
        Status: Rejected or Voided<br />
        Applicant Status Message: <?php print $answer->getPayment()->getVar('rejectedReason'); ?><br />
      </p><?php
      break;
  }?>
</fieldset>