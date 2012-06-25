<?php
/**
 * manage_mail index view
 *
 */
?>
<h2>Email Settings</h2>

<h4>Server Settings</h4>
<p>
  <strong>Type: </strong><?php print $config->getMailServerType(); ?><br />
  <strong>Host: </strong><?php print $config->getMailServeHost(); ?><br />
  <strong>Port: </strong><?php print $config->getMailServerPort(); ?><br />
  <strong>Username: </strong><?php print $config->getMailServerUsername(); ?><br />
  <strong>Password: </strong><?php print $config->getMailServerPassword(); ?><br />
</p>

<h4>Outgoing Mail</h4>
<p>
  <strong>From Address: </strong><?php print $config->getMailDefaultFromAddress(); ?><br />
  <strong>From Name: </strong><?php print $config->getMailDefaultFromName(); ?><br />
</p><?php
if ($this->controller->checkIsAllowed('manage_mail', 'test')) { ?>
  <p><a href='<?php print $this->path('manage/mail/test') ?>'>Send Test Email</a></p><?php
}