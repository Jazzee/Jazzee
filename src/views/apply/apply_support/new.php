<?php
/**
 * New view for suppoer page
 */
?>
<h1>New Message to Program</h1>
<a href='<?php print $this->controller->applyPath('support'); ?>'>All Messages</a>
<?php $this->renderElement('form', array('form' => $form));