<?php
/**
 * setup_pdftemplates index view
 *
 */
if (!empty($templates)) {?>
  <h5>Existing Application Templates:</h5>
  <ul><?php
    foreach ($templates as $template) { ?>
      <li><?php print $template->getTitle();
        if ($this->controller->checkIsAllowed('setup_pdftemplates', 'edit')) { ?>
          (<a href='<?php print $this->path('setup/pdftemplates/edit/') . $template->getId() ?>'>Edit Fields</a>)<?php
        }
        if ($this->controller->checkIsAllowed('setup_pdftemplates', 'download')) { ?>
          (<a href='<?php print $this->path('setup/pdftemplates/download/') . $template->getId() ?>'>Download</a>)<?php
        }
        if ($this->controller->checkIsAllowed('setup_pdftemplates', 'delete')) { ?>
          (<a href='<?php print $this->path('setup/pdftemplates/delete/') . $template->getId() ?>'>Delete</a>)<?php
        }?>
      </li><?php
    }?>
  </ul><?php
}
if ($this->controller->checkIsAllowed('setup_pdftemplates', 'new')) { ?>
  <p><a href='<?php print $this->path('setup/pdftemplates/new') ?>'>Add a New Template</a></p><?php
}