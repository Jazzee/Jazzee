<?php 
/**
 * setup_users index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage setup
 */
$this->renderElement('form', array('form'=>$form));?>
<?php if($results): ?>
  <h5>Results</h5>
  <ul>
  <?php foreach($results as $user): ?>
  <li><?php print $user->getLastName() . ', ' . $user->getFirstName() . ' (' . $user->getEmail() . ')' ?> 
    <?php if($this->controller->checkIsAllowed('manage_users', 'edit')): ?>
    (<a href='<?php print $this->path('admin/setup/users/programRoles/') . $user->getId()?>'>Edit</a>)
    <?php endif;?>
  </li>
  <?php endforeach;?>
  </ul>
<?php endif; ?>

<?php if($users): ?>
  <h5>Program Users</h5>
  <ul>
  <?php foreach($users as $user): ?>
  <li><?php print $user->getLastName() . ', ' . $user->getFirstName() . ' (' . $user->getEmail() . ')' ?> 
    <?php if($this->controller->checkIsAllowed('manage_users', 'edit')): ?>
    (<a href='<?php print $this->path('admin/setup/users/programRoles/') . $user->getId()?>'>Edit</a>)
    <?php endif;?>
  </li>
  <?php endforeach;?>
  </ul>
<?php endif; ?>