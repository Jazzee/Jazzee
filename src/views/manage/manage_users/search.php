<?php 
/**
 * manage_users search view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage manage
 */
$this->renderElement('form', array('form'=>$form));?>
<?php if($results): ?>
  <h5>Results</h5>
  <ul>
  <?php foreach($results as $result): ?>
  <li><?php print $result['lastName'] . ', ' . $result['firstName'] . ' (' . $result['emailAddress'] . ')' ?> 
    <?php if($this->controller->checkIsAllowed('manage_users', 'new')): ?>
    (<a href='<?php print $this->path('manage/users/new/') . $result['userName']?>'>Add User</a>)
    <?php endif;?>
  </li>
  <?php endforeach;?>
  </ul>
<?php endif; ?>