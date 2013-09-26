<?php
/**
 * payments_report index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage payment
 */
$this->renderElement('form', array('form' => $form));
if ($payments) {?>
  <table id ='payments'>
    <caption><?php print $searchResultsDescription; ?>  </caption>
    <thead><tr><th>Payment Type</th><th>Last Update</th><th>ID</th><th>Last Name</th><th>First Name</th><th>Program</th><th>Cycle</th><th>Notes</th><th>Amount</th></tr></thead>
    <tbody><?php
      foreach ($payments as $payment) { ?>
        <tr>
          <td><?php print $payment['type']['name']; ?></td>
          <td><?php print $payment['updatedAt']->format('c'); ?></td>
          <td applicantId='<?php print $payment['applicantId']; ?>' 
              programId='<?php print $payment['programId']; ?>'>
              <?php print $payment['applicantId'] ?>
          </td>
          <td><?php print $payment['applicantLastName']; ?></td>
          <td><?php print $payment['applicantFirstName']; ?></td>
          <td><?php print $payment['programName']; ?></td>
          <td><?php print $payment['cycleName']; ?></td>
          <td><?php foreach($payment['notes'] as $title => $value){?>
              <strong><?php print $title;?>:</strong>&nbsp;<?php print $value;?><br />
          <?php }?></td>
          <td><?php print $payment['amount']; ?></td>
          
        </tr><?php
      }?>
    </tbody>
      <tfoot>
        <tr>
          <th style="text-align:right" colspan="6">Total:</th>
          <th></th>
        </tr>
      </tfoot>
  </table><?php
} else if($searchResultsDescription){?>
  <p><?php print $searchResultsDescription; ?></p><?php 
}