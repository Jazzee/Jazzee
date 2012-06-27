<?php
/**
 * manage_elementtypes index view
 *
 */
if ($elementTypes) {?>
  <h5>Current Element Types:</h5>
  <ul><?php
    foreach ($elementTypes as $type) { ?>
      <li><?php print $type->getName();
        if ($this->controller->checkIsAllowed('manage_elementtypes', 'edit')) { ?>
          (<a href='<?php print $this->path('manage/elementtypes/edit/') . $type->getId() ?>'>Edit</a>)<?php
        }?>
      </li><?php
    }?>
  </ul><?php
}
if ($this->controller->checkIsAllowed('manage_elementtypes', 'new')) { ?>
  <p><a href='<?php print $this->path('manage/elementtypes/new') ?>'>Add a New Element Type</a></p><?php
}