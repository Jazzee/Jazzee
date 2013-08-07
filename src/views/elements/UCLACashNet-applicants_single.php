<?php
/**
 * Single applicant AuthorizeNetPayment view
 */
$fields = array();
$tools = array();
$fields['Amount'] = $answer->getPayment()->getAmount();
?>
<fieldset id='answer<?php print $answer->getId() ?>'>
  <legend><?php print $answer->getPayment()->getType()->getName(); ?> Payment</legend><?php
  switch ($answer->getPayment()->getStatus()) {
    case \Jazzee\Entity\Payment::PENDING:
      $fields['Status'] = 'Pending Settlement';
      $fields['Applicant Status Message'] = 'Approved';
      if ($this->controller->checkIsAllowed('applicants_single', 'settlePayment')) {
        $tools['Settle Payment'] = $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/settlePayment/' . $answer->getId());
      }
      if ($this->controller->checkIsAllowed('applicants_single', 'rejectPayment')) {
        $tools['Reject Payment'] = $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/rejectPayment/' . $answer->getId());
      }
      break;
    case \Jazzee\Entity\Payment::SETTLED:
      $fields['Status'] = 'Settled';
      $fields['Applicant Status Message'] = 'Approved';
      if ($this->controller->checkIsAllowed('applicants_single', 'refundPayment')) {
        $tools['Refund Payment'] = $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/refundPayment/' . $answer->getId());
      }
      break;
    case \Jazzee\Entity\Payment::REFUNDED:
      $fields['Status'] = 'Refunded';
      $fields['Applicant Status Message'] = $answer->getPayment()->getVar('refundedReason');
      $fields['Net Amount'] = '$' . ($answer->getPayment()->getAmount() + $answer->getPayment()->getVar('refundAmount'));
      $fields['Refund Transaction Number'] = $answer->getPayment()->getVar('refundTransactionId');
      $fields['Refund Amount'] = $answer->getPayment()->getVar('refundAmount');
    break;
    case \Jazzee\Entity\Payment::REJECTED:
      $fields['Status'] = 'Marked as Voided';
      $fields['Applicant Status Message'] = $answer->getPayment()->getVar('rejectedReason');
    break;
  }
  $fields['UCLA Reference Number'] = $answer->getPayment()->getVar('UCLA_REF_NO');
  $fields['Transaction Number'] = $answer->getPayment()->getVar('tx');
  $fields['Customer Code'] = $answer->getPayment()->getVar('custcode');
  $fields['Payment Code'] = $answer->getPayment()->getVar('pmtcode');
  $fields['Item Code'] = $answer->getPayment()->getVar('itemcode');?>
  <p><?php
  foreach($fields as $title => $value){
    print $title . ': ' . $value . '<br />';
  }?>
  </p>
  <div class='tools'><?php 
  foreach($tools as $title => $link){?>
    <a href='<?php print $link; ?>' class='actionForm'><?php print $title; ?></a><?php
  }?>
  </div><?php
?>
</fieldset>