<?php 
/**
 * setup_roles index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage setup
 */
if($roles): ?>
  <h5>Roles:</h5>
  <ul>
  <?php foreach($roles as $arr): ?>
  <li><?php print $arr['name'] ?>
    <?php if($this->controller->checkIsAllowed('setup_roles', 'edit')): ?>
      (<a href='<?php print $this->path('setup/roles/edit/') . $arr['id']?>'>Edit</a>)
    <?php endif;?>
    
  </li>
  <?php endforeach;?>
  </ul>
<?php endif; ?>
<?php if($this->controller->checkIsAllowed('setup_roles', 'new')): ?>
  <p><a href='<?php print $this->path('setup/roles/new')?>'>Add a new role</a></p>
<?php endif;?>
