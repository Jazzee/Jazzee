<?php
/**
 * setup_roles index view
 *
 */
if ($roles) {?>
  <h5>Roles:</h5>
  <ul><?php
    foreach ($roles as $role) { ?>
      <li><?php print $role->getName();
        if ($this->controller->checkIsAllowed('setup_roles', 'edit')) { ?>
          (<a href='<?php print $this->path('setup/roles/edit/') . $role->getId() ?>'>Edit</a>)<?php
        }
        if ($this->controller->checkIsAllowed('setup_roles', 'copy')) { ?>
          (<a href='<?php print $this->path('setup/roles/copy/') . $role->getId() ?>'>Copy</a>)<?php
        }?>
      </li><?php
    }?>
  </ul><?php
}
if ($this->controller->checkIsAllowed('setup_roles', 'new')) { ?>
  <p><a href='<?php print $this->path('setup/roles/new') ?>'>Add a new role</a></p><?php
}