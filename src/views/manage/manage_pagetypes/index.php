<?php 
/**
 * manage_pagetypes index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage manage
 */
if($pageTypes): ?>
  <h5>Current Page Types:</h5>
  <ul>
  <?php foreach($pageTypes as $type): ?>
  <li><?php print $type->getName() ?>
    <?php if($this->controller->checkIsAllowed('manage_pagetypes', 'edit')): ?>
      (<a href='<?php print $this->path('admin/manage/pagetypes/edit/') . $type->getId()?>'>Edit</a>)
    <?php endif;?>
    
  </li>
  <?php endforeach;?>
  </ul>
<?php endif; ?>

<?php if($this->controller->checkIsAllowed('manage_pagetypes', 'new')): ?>
  <p><a href='<?php print $this->path('admin/manage/pagetypes/new')?>'>Add a New Page Type</a></p> 
<?php endif;?>