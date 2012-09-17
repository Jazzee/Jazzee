<?php
/**
 * manage_programs index view
 *
 */
if ($activePrograms) { ?>
  <h5>Active Programs:</h5>
  <ul><?php
    foreach ($activePrograms as $program) { ?>
      <li><?php print $program->getName();
        if ($this->controller->checkIsAllowed('manage_programs', 'edit')) { ?>
          (<a href='<?php print $this->path('manage/programs/edit/') . $program->getId() ?>'>Edit</a>)<?php
        }
        if ($this->controller->checkIsAllowed('manage_programs', 'expire')) { ?>
          (<a href='<?php print $this->path('manage/programs/expire/') . $program->getId() ?>'>Expire</a>)<?php
        }?>
      </li><?php
    }?>
  </ul><?php
}

if ($expiredPrograms) { ?>
  <h5>Expired Programs:</h5>
  <ul><?php
    foreach ($expiredPrograms as $program) { ?>
      <li><?php print $program->getName();
        if ($this->controller->checkIsAllowed('manage_programs', 'activate')) { ?>
          (<a href='<?php print $this->path('manage/programs/activate/') . $program->getId() ?>'>Activate</a>)<?php
        }?>
      </li><?php
    }?>
  </ul><?php
}

if ($this->controller->checkIsAllowed('manage_programs', 'new')) { ?>
  <p><a href='<?php print $this->path('manage/programs/new') ?>'>Add a New Program</a></p><?php
}