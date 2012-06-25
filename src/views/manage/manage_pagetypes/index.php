<?php
/**
 * manage_pagetypes index view
 *
 */
if ($pageTypes) {?>
  <h5>Current Page Types:</h5>
  <ul><?php
    foreach ($pageTypes as $type) { ?>
      <li><?php print $type->getName();
      if ($this->controller->checkIsAllowed('manage_pagetypes', 'edit')) { ?>
        (<a href='<?php print $this->path('manage/pagetypes/edit/') . $type->getId() ?>'>Edit</a>)<?php
      }?>
      </li><?php
    }?>
  </ul><?php
}
if ($this->controller->checkIsAllowed('manage_pagetypes', 'new')) { ?>
  <p><a href='<?php print $this->path('manage/pagetypes/new') ?>'>Add a New Page Type</a></p><?php
}