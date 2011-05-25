<?php 
/**
 * List of programs
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
?>
<fieldset>
  <legend>Select the program you are applying to:</legend>
  <ul>
  <?php foreach($programs as $program): ?>
    <li><a href='<?php print $this->path('apply/' . $program->getShortName() . '/');?>'><?php print $program->getName(); ?></a></li>
  <?php endforeach; ?>
	</ul>
</fieldset>
