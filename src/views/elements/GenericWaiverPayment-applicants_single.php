<?php
/**
 * Single applicant Needbased Waiver view
 */
?>
<fieldset id='answer<?print $answer->getId() ?>'>
  <legend><?php print $answer->getPayment()->getType()->getName(); ?> Payment</legend>
  <?php 
    switch($answer->getPayment()->getStatus()){
      case \Jazzee\Entity\Payment::PENDING:?>
        <p>
          <strong>Amount:</strong>&nbsp;$<?php print $answer->getPayment()->getAmount();?><br />
          <strong>Status: </strong>Request Pending<br />
          <strong>Justification: </strong><?php print nl2br($answer->getPayment()->getVar('justification'));?><br />
        </p>
        <div class='tools'>
          <?php if($this->controller->checkIsAllowed('applicants_single', 'settlePayment')){ ?>
            <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/settlePayment/' . $answer->getId());?>' class='actionForm'>Approve Waiver</a>
          <?php } ?>
          <?php if($this->controller->checkIsAllowed('applicants_single', 'rejectPayment')){ ?>
            <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/rejectPayment/' . $answer->getId());?>' class='actionForm'>Deny Waiver</a>   
          <?php } ?>
          <?php if($this->controller->checkIsAllowed('applicants_single', 'refundPayment')){ ?>
            <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/refundPayment/' . $answer->getId());?>' class='actionForm'>Withdraw Request</a>   
          <?php } ?>
        </div>
      <?php break;
      case \Jazzee\Entity\Payment::SETTLED:?>
        <p>
          <strong>Amount:</strong>&nbsp;$<?php print $answer->getPayment()->getAmount();?><br />
          Status: Approved<br />
        </p>
        <div class='tools'>
          <?php if($this->controller->checkIsAllowed('applicants_single', 'refundPayment')){ ?>
            <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/refundPayment/' . $answer->getId());?>' class='actionForm'>Withdraw Request</a>   
          <?php } ?>
        </div>
      <?php break;
      case \Jazzee\Entity\Payment::REFUNDED:?>
        <p>
          <strong>Amount:</strong>&nbsp;$<?php print $answer->getPayment()->getAmount();?><br />
          Status: Withdrawn<br />
          Reason: <?php print $answer->getPayment()->getVar('refundedReason'); ?><br />
        </p>
      <?php break;
      case \Jazzee\Entity\Payment::REJECTED:?>
        <p>
          <strong>Amount:</strong>&nbsp;$<?php print $answer->getPayment()->getAmount();?><br />
          Status: Denied<br/>
          Reason: <?php print $answer->getPayment()->getVar('rejectedReason'); ?><br />
        </p>
      <?php break;
    }
  ?>
</fieldset>