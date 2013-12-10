<?php
/**
 * setup_previewapplication index view
 *
 */
if (!empty($existing)) {?>
  <h5>Existing Application Previews:</h5>
  <ul><?php
    foreach ($existing as $preview) { ?>
      <li><?php print $preview['lastAccessed'];?> <a href='<?php print $preview['link'];?>'>Link</a><?php
        if ($this->controller->checkIsAllowed('setup_previewapplication', 'delete')) { ?>
          (<a href='<?php print $this->path('setup/previewapplication/delete/') . $preview['key'] ?>'>Delete</a>)<?php
        }?>
      </li><?php
    }?>
  </ul><?php
}
if ($this->controller->checkIsAllowed('setup_previewapplication', 'new')) { ?>
  <p><a href='<?php print $this->path('setup/previewapplication/new') ?>'>Add a New Preview</a></p><?php
}