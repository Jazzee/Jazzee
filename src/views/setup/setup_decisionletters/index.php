<?php
/**
 * setup_decisionletters index view
 *
 */
if (count($admitTemplates)) {?>
  <h5>Admit Templates:</h5>
  <ul><?php
    foreach ($admitTemplates as $template) { ?>
      <li><?php print $template->getTitle();
        if ($this->controller->checkIsAllowed('setup_decisionletters', 'copy')) { ?>
          <a href='<?php print $this->path('setup/decisionletters/copy/') . $template->getId() ?>'>Copy</a><?php
        }
        if ($this->controller->checkIsAllowed('setup_decisionletters', 'edit')) { ?>
          <a href='<?php print $this->path('setup/decisionletters/edit/') . $template->getId() ?>'>Edit</a><?php
        }
        if ($this->controller->checkIsAllowed('setup_decisionletters', 'delete')) { ?>
          <a href='<?php print $this->path('setup/decisionletters/delete/') . $template->getId() ?>'>Delete</a><?php
        }?>
      </li><?php
    }?>
  </ul><?php
}
if (count($denyTemplates)) {?>
  <h5>Deny Templates:</h5>
  <ul><?php
    foreach ($denyTemplates as $template) { ?>
      <li><?php print $template->getTitle();
        if ($this->controller->checkIsAllowed('setup_decisionletters', 'copy')) { ?>
          <a href='<?php print $this->path('setup/decisionletters/copy/') . $template->getId() ?>'>Copy</a><?php
        }
        if ($this->controller->checkIsAllowed('setup_decisionletters', 'edit')) { ?>
          <a href='<?php print $this->path('setup/decisionletters/edit/') . $template->getId() ?>'>Edit</a><?php
        }
        if ($this->controller->checkIsAllowed('setup_decisionletters', 'delete')) { ?>
          <a href='<?php print $this->path('setup/decisionletters/delete/') . $template->getId() ?>'>Delete</a><?php
        }?>
      </li><?php
    }?>
  </ul><?php
}
if ($this->controller->checkIsAllowed('setup_decisionletters', 'new')) { ?>
  <p><a href='<?php print $this->path('setup/decisionletters/new') ?>'>Add a New Decision Template</a></p><?php
}
