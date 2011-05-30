<?php 
/**
 * manage_users index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage manage
 */
$this->renderElement('form', array('form'=>$form));?>
<?php if($results): ?>
  <h5>Results</h5>
  <ul>
  <?php foreach($results as $user): ?>
  <li><?php print $user->getLastName() . ', ' . $user->getFirstName() . ' (' . $user->geteduPersonPrincipalName() . ')' ?> 
    <?php if($this->controller->checkIsAllowed('manage_users', 'edit')): ?>
    (<a href='<?php print $this->path('manage/users/edit/') . $user->getId()?>'>Edit</a>)
    <?php endif;?>
  </li>
  <?php endforeach;?>
  </ul>
<?php endif; ?>
<?php if($this->controller->checkIsAllowed('manage_users', 'new')): ?>
<p><a href='<?php print $this->path('manage/users/new')?>'>Add a New User</a></p>
<?php endif;?>