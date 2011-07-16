<?php
/**
 * New view for suppoer page
 */
?>
<h1>New Message to Program</h1>
<a href='<?php print $this->path($basePath . '/support');?>'>All Messages</a>
<?php $this->renderElement('form', array('form'=> $form)); ?>