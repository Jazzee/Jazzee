<?php
/**
 * setup_publishapplication index view
 *
 */
?>
<h2>Publication Status</h2><?php
if ($published) { ?>
  <p>The application has been published</p><?php
  if ($this->controller->checkIsAllowed('setup_publishapplication', 'unpublish')) { ?>
    <p><a href='<?php print $this->path('setup/publishapplication/unpublish') ?>'>Un-Publish Application</a></p><?php
  }
} else {
  if ($ready) { ?>
    <p>Your application is ready to be published.</p><?php
    if ($this->controller->checkIsAllowed('setup_publishapplication', 'publish')) { ?>
      <p><a href='<?php print $this->path('setup/publishapplication/publish') ?>'>Publish Application</a></p><?php
    }
  } else {
    if(!empty($blockers)){ ?>
      <p>Your application has the following problems which can NOT be ignored:</p>
      <ul><?php
        foreach ($blockers as $b) { ?>
          <li><?php print $b; ?></li><?php
        } ?>
      </ul><?php
    }
    if(!empty($problems)){ ?>
      <p>Your application has the following problems which can be ignored:</p>
      <ul><?php
        foreach ($problems as $p) { ?>
          <li><?php print $p; ?></li><?php
        } ?>
      </ul><?php
    }
    if (empty($blockers) and $this->controller->checkIsAllowed('setup_publishapplication', 'publishoverride')) { ?>
      <p><a href='<?php print $this->path('setup/publishapplication/publishoverride') ?>'>Ignore problems and publish application</a></p><?php
    }

  }
}