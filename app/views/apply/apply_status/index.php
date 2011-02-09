<?php 
/**
 * apply_status view
 * @package jazzee
 * @subpackage apply
 */
if(!$applicant->locked):?>
  <h2>Application Status: <em>Not Complete</em></h2>
  <p>Your application was not completed before the application deadline and will not be reviewed.</p>
<?php elseif($status['decline']):?>
  <h2>Application Status: <em>Declined</em></h2>
  <p>You have declined our offer of admission or you have missed the deadline for enrollment confirmation.</p>
<?php elseif($status['accept']):?>
  <h2>Application Status: <em>Accepted</em></h2>
<?php elseif($status['deny']):?>
  <h2>Application Status: <em>Denied</em></h2>
<?php elseif($status['admit']):?>
  <h2>Application Status: <em>Admitted</em></h2>
<?php else:?>
  <h2>Application Status: <em>Under Review</em></h2>
<?php endif;?>
<?php foreach($answerStatusPages as $page) $this->renderElement(get_class($page) . '-answer_status', array('page'=> $page)); ?>