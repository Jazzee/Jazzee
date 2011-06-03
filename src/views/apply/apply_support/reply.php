<?php
/**
 * New view for suppoer page
 */
?>
<h1>Reply to message</h1>
<div class='threaded'>
  <p class='header'>On <?php print $message->getCreatedAt()->format('l F jS Y \a\t g:ia') ?>
    <?php if($message->getSender() == 'applicant'){?>you <?php } else {?> your program <?php } ?> said</p>
    <p><?php print $message->getText();?></p>
</div>
<?php $this->renderElement('form', array('form'=> $form)); ?>