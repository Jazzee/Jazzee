<?php 
/**
 * manage_roles index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage manage
 */
if($roles): ?>
  <h5>Roles:</h5>
  <ul>
  <?php foreach($roles as $arr): ?>
  <li><?php print $arr['name'] ?>
    <?php if($this->controller->checkIsAllowed('manage_roles', 'edit')): ?>
      (<a href='<?php print $this->path('manage/roles/edit/') . $arr['id']?>'>Edit</a>)
    <?php endif;?>
    
  </li>
  <?php endforeach;?>
  </ul>
<?php endif; ?>
<?php if($this->controller->checkIsAllowed('manage_roles', 'new')): ?>
  <p><a href='<?php print $this->path('manage/roles/new')?>'>Add a new role</a></p>
<?php endif;?>
