<?php
/**
 * manage_answerstatus index view
 *
 */
if ($statuses) {?>
  <h5>Answer Status Options:</h5>
  <ul><?php
    foreach ($statuses as $status) { ?>
      <li><?php print $status->getName();
        if ($this->controller->checkIsAllowed('manage_answerstatus', 'edit')) { ?>
          (<a href='<?php print $this->path('manage/answerstatus/edit/') . $status->getId() ?>'>Edit</a>)<?php
        }
        if ($this->controller->checkIsAllowed('manage_answerstatus', 'delete')) { ?>
          (<a href='<?php print $this->path('manage/answerstatus/delete/') . $status->getId() ?>'>Delete</a>)<?php
        }?>
      </li><?php
    }?>
  </ul><?php
}
if ($this->controller->checkIsAllowed('manage_answerstatus', 'new')) { ?>
  <p><a href='<?php print $this->path('manage/answerstatus/new') ?>'>Add a New Answer Status Option</a></p><?php
}