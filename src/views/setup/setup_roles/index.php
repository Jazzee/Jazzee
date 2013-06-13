<?php
/**
 * setup_roles index view
 *
 */
if ($roles) {?>
  <h5>Roles:</h5>
  <ul id='roleList'><?php
    foreach ($roles as $role) { ?>
      <li id="role<?php print $role->getId();?>"><?php print $role->getName();
        if($role->getDisplay()){
          print ' <span>(Limited Display)</span>';
        } else {
          print ' <span>(Full Applicant Display)</span>';
        }
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