<?php
/**
 * apply_account index view
 * @package jazzee
 * @subpackage apply
 */
?>
<h3>Your Account</h3>
<p>
  <strong>Name:</strong><?php print $applicant->getFullName();?>
  <?php if($allowNameChange){?>
    <a href='<?php print $this->controller->applyPath('account/changeName'); ?>'> Change Name</a>
  <?php } ?>
  <br />
  <strong>Email Address:</strong><?php print $applicant->getEmail();?>
  <?php if($allowEmailChange){?>
    <a href='<?php print $this->controller->applyPath('account/changeEmail'); ?>'> Change Email</a>
  <?php } ?><br />
  <?php if($allowPasswordChange){?>
    <a href='<?php print $this->controller->applyPath('account/changePassword'); ?>'>Change Password</a>
  <?php } ?>
<br/><br/>
  <?php if($allowPrintApplication){?>
    <strong>Print Application:</strong>
    <a href='<?php print $this->controller->applyPath('account/printApplication'); ?>'> Print</a>
  <?php } ?><br />

</p>
