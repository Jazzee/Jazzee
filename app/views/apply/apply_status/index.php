<?php 
/**
 * apply_status view
 * @package jazzee
 * @subpackage apply
 */
if(!$applicant->locked):?>
  <h2>Application Status: <em>Not Complete</em></h2>
  <p>Your application was not completed before the application deadline and will not be reviewed.</p>
<?php elseif(!$status):?>
  <h2>Application Status: <em>Under Review</em></h2>
<?php elseif((ApplyStatusController::DECLINED & $status) == true):?>
  <h2>Application Status: <em>Declined</em></h2>
<?php elseif((ApplyStatusController::ACCEPTED & $status) == true):?>
  <h2>Application Status: <em>Accepted</em></h2>
<?php elseif((ApplyStatusController::DENIED & $status) == true):?>
  <h2>Application Status: <em>Denied</em></h2>
<?php elseif((ApplyStatusController::ADMITTED & $status) == true):?>
  <h2>Application Status: <em>Admitted</em></h2>
<?php endif;?>