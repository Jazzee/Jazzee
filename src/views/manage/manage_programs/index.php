<?php 
/**
 * manage_programs index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage manage
 */
if($programs): ?>
  <h5>Programs:</h5>
  <ul>
  <?php foreach($programs as $program): ?>
  <li><?php print $program->getName() ?>
  <?php if($this->controller->checkIsAllowed('manage_programs', 'edit')): ?>
    (<a href='<?php print $this->path('manage/programs/edit/') . $program->getId()?>'>Edit</a>)
  <?php endif;?>
    
  </li>
  <?php endforeach;?>
  </ul>
<?php endif; ?>
<?php if($this->controller->checkIsAllowed('manage_programs', 'new')): ?>
  <p><a href='<?php print $this->path('manage/programs/new')?>'>Add a New Program</a></p>
<?php endif;?>
