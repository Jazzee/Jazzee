<?php
/**
 * manage_programs index view
 *
 */
if ($programs) { ?>
  <h5>Programs:</h5>
  <ul><?php
    foreach ($programs as $program) { ?>
      <li><?php print $program->getName();
        if ($this->controller->checkIsAllowed('manage_programs', 'edit')) { ?>
          (<a href='<?php print $this->path('manage/programs/edit/') . $program->getId() ?>'>Edit</a>)<?php
        }?>
      </li><?php
    }?>
  </ul><?php
}
if ($this->controller->checkIsAllowed('manage_programs', 'new')) { ?>
  <p><a href='<?php print $this->path('manage/programs/new') ?>'>Add a New Program</a></p><?php
}