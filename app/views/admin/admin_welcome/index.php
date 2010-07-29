<?php 
/**
 * admin_welcome view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 */
 ?>
 <p>Welcome <?php print $firstName . ' ' . $lastName ?><br />
<?php if($failedLoginAttempts): ?>
  Someone (maybe you) attempted to access this account <strong><?php print $failedLoginAttempts ?> times</strong> 
  with an incorrect password from <strong><?php print getHostByAddr($lastFailedLogin_ip) ?></strong>.<br />
<?php endif;
if($lastLogin): ?>
  Last successfull login: <strong><?php print date('l F jS Y g:ia', strtotime($lastLogin)) ?></strong> 
  from <strong><?php print getHostByAddr($lastLogin_ip) ?></strong><br />
  If this is not correct your account may have been compromised and you should let someone know immediately.
<?php endif; ?>
</p>