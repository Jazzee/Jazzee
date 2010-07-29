<?php 
/**
 * admin_profile index view
 * Display profile information and links to edit
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 */
?>
<fieldset>
  <legend>Your Profile</legend>
  <p>
    <strong>Name: </strong><?php print $user->firstName ?> <?php print $user->lastName ?> <br />
    <strong>Default Program: </strong><?php print $defaultProgram ?> <br />
    <strong>Default Cycle: </strong><?php print $defaultCycle ?> <br />
  <?php if($this->controller->checkIsAllowed('admin_profile', 'editProfile')): ?>
  <p><a href='<?php print $this->path('admin/profile/editProfile')?>'>Edit Profile</a></p>
  <?php endif;?>
  
</fieldset>
<?php if($this->controller->checkIsAllowed('admin_profile', 'changePassword')): ?>
  <p><a href='<?php print $this->path('admin/profile/changePassword')?>'>Change Password</a></p>
<?php endif;?>
