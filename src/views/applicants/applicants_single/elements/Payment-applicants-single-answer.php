<?php 
/**
 * Payment single answer
 */
?>
<tr id='answer<?print $answer->getId() ?>'>

<td>
  <?php 
    $payment = $answer->getPayment();
    print '<strong>Type:</strong>&nbsp;' . $payment->getType()->getName() . '<br />';
    print '<strong>Amount:</strong>&nbsp;$' . $payment->getAmount();
  ?>
</td><td>
  <?php 
    $class = $payment->getType()->getClass();
    switch($payment->getStatus()){
      case \Jazzee\Entity\Payment::PENDING:
        $applicantStatus = $class::PENDING_TEXT;
        $status = \Jazzee\PaymentType\AbstractPaymentType::PENDING_TEXT;
        break;
      case \Jazzee\Entity\Payment::SETTLED:
        $applicantStatus = $class::SETTLED_TEXT;
        $status = \Jazzee\PaymentType\AbstractPaymentType::SETTLED_TEXT;
        break;
      case \Jazzee\Entity\Payment::REJECTED:
        $applicantStatus = $class::REJECTED_TEXT;
        $status = \Jazzee\PaymentType\AbstractPaymentType::REJECTED_TEXT;
        $status .= '<br />Reason: ' . $payment->getVar('rejectedReason');
        break;
      case \Jazzee\Entity\Payment::REFUNDED:
        $applicantStatus = $class::REFUNDED_TEXT;
        $status = \Jazzee\PaymentType\AbstractPaymentType::REFUNDED_TEXT;
        break;
    }
  ?>
  Status: <?php print $status; ?> <br />
  Applicant Status Message: <?php print $applicantStatus; ?>
</td>
<?php if($this->controller->checkIsAllowed('applicants_single', 'settlePayment') or $this->controller->checkIsAllowed('applicants_single', 'refundPayment') or $this->controller->checkIsAllowed('applicants_single', 'rejectPayment')){ ?>
  <td>
    <?php if($this->controller->checkIsAllowed('applicants_single', 'settlePayment') and $payment->getStatus() == \Jazzee\Entity\Payment::PENDING){ ?>
      <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/settlePayment/' . $answer->getId());?>' class='actionForm'>Settle</a><br />     
    <?php } ?>
    <?php if($this->controller->checkIsAllowed('applicants_single', 'refundPayment') and ($payment->getStatus() == \Jazzee\Entity\Payment::PENDING or $payment->getStatus() == \Jazzee\Entity\Payment::SETTLED)){ ?>
      <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/refundPayment/' . $answer->getId());?>' class='actionForm'>Refund</a><br />     
    <?php } ?>
    <?php if($this->controller->checkIsAllowed('applicants_single', 'rejectPayment') and ($payment->getStatus() == \Jazzee\Entity\Payment::PENDING or $payment->getStatus() == \Jazzee\Entity\Payment::SETTLED)){ ?>
      <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/rejectPayment/' . $answer->getId());?>' class='actionForm'>Reject</a><br />     
    <?php } ?>
  </td>
<?php }?>
</tr>