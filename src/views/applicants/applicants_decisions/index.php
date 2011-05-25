<?php 
/**
 * applicants_decisions index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
?>
<?php if(!empty($list['noDecision'])):?>
<div class ='form' id='noDecision'>
  <h4>Applicants With No Decision</h4>
  <form method='post' action='<?php print $this->controller->path("applicants/decisions/preliminaryDecision"); ?>'>
  <table>
		<thead>
		  <tr>
		  	<th>Name</th><th>Admit<br /><input id='preliminaryAdmitAll' type='checkbox' /></th><th>Deny<br /><input id='preliminaryDenyAll' type='checkbox' /></th></tr>
		</thead>
		<tbody>
		<?php foreach($list['noDecision'] as $applicant):?>
		<tr>
		  <td><?php print "{$applicant->lastName}, {$applicant->firstName} {$applicant->middleName}"?></td>
		  <td><input type='checkbox' name='admit[]' value='<?php print $applicant->id ?>' class='preliminaryAdmit' /></td>
		  <td><input type='checkbox' name='deny[]' value='<?php print $applicant->id ?>' class='preliminaryDeny' /></td>
		<?php endforeach;?>
		</tbody>
	</table>
	<input type='submit' value='Save' />
	</form>
</div>
<?php endif; ?>

<?php if(!empty($list['nominateDeny'])):?>
<div class ='form' id='nominateDeny'>
<h4>Applicants Nominated for Deny</h4>
  <form method='post' action='<?php print $this->controller->path("applicants/decisions/finalDeny"); ?>'>
  <table>
		<thead>
		  <tr>
		  	<th>Name</th><th>Undo Decision<br /><input id='undoPreliminaryDenyAll' type='checkbox' /></th><th>Final Deny<br /><input id='finalDenyAll' type='checkbox' /></th></tr>
		</thead>
		<tbody>
		<?php foreach($list['nominateDeny'] as $applicant):?>
		<tr>
		  <td><?php print "{$applicant->lastName}, {$applicant->firstName} {$applicant->middleName}"?></td>
		  <td><input type='checkbox' name='undo[]' value='<?php print $applicant->id ?>' class='undoPreliminaryDeny' /></td>
		  <td><input type='checkbox' name='deny[]' value='<?php print $applicant->id ?>' class='finalDeny' /></td>
		  </tr>
		<?php endforeach;?>
		</tbody>
	</table>
	<input type='submit' value='Save' />
	</form>
</div>
<?php endif; ?>

<?php if(!empty($list['nominateAdmit'])):?>
<div class ='form' id='nominateAdmit'>
<h4>Applicants Nominated for Admit</h4>
  <form method='post' action='<?php print $this->controller->path("applicants/decisions/finalAdmit"); ?>'>
  <table>
		<thead>
		  <tr>
		  	<th>Name</th><th>Undo Decision<br /><input id='undoPreliminaryAdmitAll' type='checkbox' /></th><th>Final Admit<br /><input id='finalAdmitAll' type='checkbox' /></th></tr>
		</thead>
		<tbody>
		<?php foreach($list['nominateAdmit'] as $applicant):?>
		<tr>
		  <td><?php print "{$applicant->lastName}, {$applicant->firstName} {$applicant->middleName}"?></td>
		  <td><input type='checkbox' name='undo[]' value='<?php print $applicant->id ?>' class='undoPreliminaryAdmit' /></td>
		  <td><input type='checkbox' name='admit[]' value='<?php print $applicant->id ?>' class='finalAdmit' /></td>
		</tr>
		<?php endforeach;?>
		</tbody>
	</table>
	<label for='sirdeadline' class='required'>SIR Deadline:</label><input id='sirdeadline' type='text' name='sirdeadline' /><br />
	<input type='submit' value='Save' />
	</form>
</div>
<?php endif; ?>


<?php if(!empty($list['finalAdmit'])):?>
<div id='finalAdmit'>
<h4>Admitted Applicants</h4>
  <table>
		<thead>
		  <tr>
		  	<th>Name</th><th>Offer Deadline</th><th>Decision Letter</th><th>Offer Status</th></tr>
		</thead>
		<tbody>
		<?php foreach($list['finalAdmit'] as $applicant):?>
		<tr>
		  <td><?php print "{$applicant->lastName}, {$applicant->firstName} {$applicant->middleName}"; ?></td>
		  <td><?php $this->renderElement('long_date', array('date' =>$applicant->Decision->offerResponseDeadline)) ?></td>
		  <td>
		  <?php if($applicant->Decision->decisionLetterViewed){
		    print 'Viewed: '; $this->renderElement('long_date', array('date' =>$applicant->Decision->decisionLetterViewed));
		  } else if($applicant->Decision->decisionLetterSent){
		    print 'Sent: '; $this->renderElement('long_date', array('date' =>$applicant->Decision->decisionLetterSent));
		  }
		  ?>
		  </td>
		  <td>
		  <?php if($applicant->Decision->acceptOffer){
		    print 'Accepted Offer  '; $this->renderElement('long_date', array('date' =>$applicant->Decision->acceptOffer));
		  } else if($applicant->Decision->declineOffer){
		    print 'Declined Offer '; $this->renderElement('long_date', array('date' =>$applicant->Decision->declineOffer));
		  } else {
		    print "No Decision";
		  } 
		  ?>
		  </td>
		</tr>
		<?php endforeach;?>
		</tbody>
	</table>
</div>
<?php endif; ?>

<?php if(!empty($list['finalDeny'])):?>
<div id='finalDeny'>
<h4>Denied Applicants</h4>
  <ul>
  	<?php foreach($list['finalDeny'] as $applicant):?>
  	<li><?php print "{$applicant->lastName}, {$applicant->firstName} {$applicant->middleName}"; ?></li>
		<?php endforeach;?>
	</ul>
</div>
<?php endif; ?>