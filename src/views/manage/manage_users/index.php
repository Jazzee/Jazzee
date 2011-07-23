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
  <li><?php print $user->getLastName() . ', ' . $user->getFirstName() . ' (' . $user->getUniqueName() . ')' ?> 
    <?php if($this->controller->checkIsAllowed('manage_users', 'edit')): ?>
    (<a href='<?php print $this->path('manage/users/edit/') . $user->getId()?>'>Edit</a>)
    <?php endif;?>
    <?php if($this->controller->checkIsAllowed('manage_users', 'refreshUser')): ?>
    (<a href='<?php print $this->path('manage/users/refreshUser/') . $user->getId()?>'>Refresh from Directory</a>)
    <?php endif;?>
  </li>
  <?php endforeach;?>
  </ul>
<?php endif; ?>
<?php if($this->controller->checkIsAllowed('manage_users', 'search')): ?>
<p><a href='<?php print $this->path('manage/users/search')?>'>Add a New User</a></p>
<?php endif;?>