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
  <?php foreach($results as $arr): ?>
  <li><?php print $arr['lastName'] . ', ' . $arr['firstName'] ?> 
    <?php if($this->controller->checkIsAllowed('manage_users', 'edit')): ?>
    (<a href='<?php print $this->path('manage/users/edit/') . $arr['id']?>'>Edit</a>)
    <?php endif;?>
    <?php if($this->controller->checkIsAllowed('manage_users', 'reset')): ?>
    (<a href='<?php print $this->path('manage/users/reset/') . $arr['id']?>'>Reset Password</a>)
    <?php endif;?>
  </li>
  <?php endforeach;?>
  </ul>
<?php endif; ?>
<?php if($this->controller->checkIsAllowed('manage_users', 'new')): ?>
<p><a href='<?php print $this->path('manage/users/new')?>'>Add a New User</a></p>
<?php endif;?>