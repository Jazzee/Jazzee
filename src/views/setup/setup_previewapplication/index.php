<?php
/**
 * setup_previewapplication index view
 *
 */
?>
<p>Previews rely on your browser remembering that it is in preview mode to work correctly.  If you close your browser or
switch to a different window this can cause problems with the preview system.  This is not a problem when follwing links
within the application, however if you are testing functionality that relies on receiving a link via email that 
link may not work correctly unless the following steps are taken:
<ol>
    <li>Copy the link sent by the application from your email client</li>
    <li>Click the preview link below to put your browser in preview mode.  If you have multiple preview links you must
    use the same one you used when sending the email link</li>
    <li>In the same browser window and tab paste in the link you received in your email</li>
</ol>
</p>
<?php
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