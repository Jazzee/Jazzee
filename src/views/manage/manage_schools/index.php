<?php
/**
 * manage_schools index view
 *
 */
if (isset($form)) {
  $this->renderElement('form', array('form' => $form));
}
if (isset($schools)) {?>
  <h5>Schools:</h5>
  <ul><?php
    foreach ($schools as $school) { ?>
      <li><?php print $school->getName();
        if ($this->controller->checkIsAllowed('manage_schools', 'edit')) { ?>
          (<a href='<?php print $this->path('manage/schools/edit/') . $school->getId() ?>'>Edit</a>)<?php
        }
        if ($this->controller->checkIsAllowed('manage_schools', 'delete')) { ?>
          (<a href='<?php print $this->path('manage/schools/delete/') . $school->getId() ?>'>Delete</a>)<?php
        }?></li><?php
    }?>
  </ul><?php
}
if ($this->controller->checkIsAllowed('manage_schools', 'new')) { ?>
  <p><a href='<?php print $this->path('manage/schools/new') ?>'>Add a New School</a></p><?php
}