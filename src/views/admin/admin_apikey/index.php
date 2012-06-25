<?php
/**
 * admin_apikey index view
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
?>
<p>Your Key: <strong><?php print $apiKey; ?></strong></p>
<?php if ($this->controller->checkIsAllowed('admin_apikey', 'updateKey')) { ?>
  <a href='<?php print $this->controller->path('apikey/updateKey'); ?>'>Update Key</a>
<?php
}