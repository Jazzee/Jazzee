<?php 
/**
 * manage_virtualfiles index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage manage
 */
if($files): ?>
  <h5>Files:</h5>
  <ul>
  <?php foreach($files as $file): ?>
  <li><?php print $file->getName() ?>
    (<a href='<?php print $this->path('virtualfile/'.$file->getName())?>'>Preview</a>)
    <?php if($this->controller->checkIsAllowed('manage_virtualfiles', 'edit')): ?>
    (<a href='<?php print $this->path('manage/virtualfiles/edit/') . $file->getId()?>'>Edit</a>)
    <?php endif;?>
    <?php if($this->controller->checkIsAllowed('manage_virtualfiles', 'delete')): ?>
    (<a href='<?php print $this->path('manage/virtualfiles/delete/') . $file->getId()?>'>Delete</a>)
    <?php endif;?>
  </li>
  <?php endforeach;?>
  </ul>
<?php endif; ?>
<?php if($this->controller->checkIsAllowed('manage_virtualfiles', 'new')): ?>
<p><a href='<?php print $this->path('manage/virtualfiles/new')?>'>Add a New Virtual File</a></p>
<?php endif;?>
