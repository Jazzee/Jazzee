<?php 
/**
 * manage_cycles index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage manage
 */
if($cycles): ?>
  <h5>Current Cycles:</h5>
  <ul>
  <?php foreach($cycles as $cycle): ?>
  <li><?php print $cycle->getName() ?>
    <?php if($this->controller->checkIsAllowed('manage_cycles', 'edit')): ?>
    (<a href='<?php print $this->path('admin/manage/cycles/edit/') . $cycle->getId()?>'>Edit</a>)
    <?php endif;?>
  </li>
  <?php endforeach;?>
  </ul>
<?php endif; ?>
<?php if($this->controller->checkIsAllowed('manage_cycles', 'new')): ?>
<p><a href='<?php print $this->path('admin/manage/cycles/new')?>'>Add a New Cycle</a></p>
<?php endif;?>
