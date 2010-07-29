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
  <?php foreach($cycles as $arr): ?>
  <li><?php print $arr['name'] ?>
    <?php if($this->controller->checkIsAllowed('manage_cycles', 'edit')): ?>
    (<a href='<?php print $this->path('manage/cycles/edit/') . $arr['id']?>'>Edit</a>)
    <?php endif;?>
  </li>
  <?php endforeach;?>
  </ul>
<?php endif; ?>
<?php if($this->controller->checkIsAllowed('manage_cycles', 'new')): ?>
<p><a href='<?php print $this->path('manage/cycles/new')?>'>Add a New Cycle</a></p>
<?php endif;?>
