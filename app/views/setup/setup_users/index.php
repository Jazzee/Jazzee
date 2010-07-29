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
  <?php foreach($results as $arr): ?>
  <li><?php print $arr['lastName'] . ', ' . $arr['firstName'] ?> 
    <?php if($this->controller->checkIsAllowed('setup_users', 'programRoles')): ?>
    (<a href='<?php print $this->path('setup/users/programRoles/') . $arr['id']?>'>Modify Program Role</a>)
    <?php endif;?>
  </li>
  <?php endforeach;?>
  </ul>
<?php endif; ?>