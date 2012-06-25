<?php
/**
 * applicants_messages index view
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
?>
<p><a href='<?php print $this->path('applicants/messages/new'); ?>'>Create New Message</a></p>
<h3>Unread Messages</h3>
<p><a href='<?php print $this->path('applicants/messages'); ?>'>New Messages Only</a></p>
<?php $this->renderElement('applicants_messages_list', array('threads' => $threads));