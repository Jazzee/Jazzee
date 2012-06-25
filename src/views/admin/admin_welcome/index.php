<?php
/**
 * admin_welcome view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 */
?>
<p>Welcome to the online application system.
  <?php
    if (isset($user)) { ?>
      You are currently logged in as
      <?php print $user->getFirstName() . ' ' . $user->getLastName(); ?>.
  <?php
    } else {
  ?>
    You must <a href='<?php print $this->controller->path('login'); ?>'>Login</a> to access your account.
  <?php
    }
  ?>
</p>