<?php
/**
 * List of programs
 *
 */
?>
<fieldset>
  <legend>Select the program you are applying to:</legend>
  <ul class='nobullets'>
    <?php
    foreach ($programs as $program) {?>
      <li><a href='<?php print $this->path('apply/' . $program->getShortName()); ?>'><?php print $program->getName(); ?></a></li>
    <?php
    }?>
  </ul>
</fieldset>
