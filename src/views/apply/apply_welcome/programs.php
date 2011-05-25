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
  <?php foreach($programs as $shortName => $name): ?>
    <li><a href='<?php print $this->path("apply/{$shortName}/");?>'><?php print $name; ?></a></li>
  <?php endforeach; ?>
	</ul>
</fieldset>
