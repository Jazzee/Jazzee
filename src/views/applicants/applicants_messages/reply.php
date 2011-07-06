<?php
/**
 * applicants_messages reply view
 */
$message = $message->getFirstMessage();
$count = 0;
?>
<h1>Reply to message</h1>
<div class='threaded'>
  <?php do{ ?>
    <div class='<?php print ($message->isRead(\Jazzee\Entity\Message::PROGRAM)?'read':'unread'); ?>'>
      <h4><?php print $message->getSubject();?></h4>
      <p class='header'>On <?php print $message->getCreatedAt()->format('l F jS Y \a\t g:ia') ?> 
      <?php if($message->getSender() == \Jazzee\Entity\Message::APPLICANT){
        print $message->getApplicant()->getFullName();
      } else {
        print 'your program';
      } ?> said</p>
      <p><?php print $message->getText();?></p>
  <?php $count++; ?>
  <?php } while ($message = $message->getReply());?>
  <?php for($i = 0; $i<$count; $i++) print '</div>';?>
</div>
<?php $this->renderElement('form', array('form'=> $form)); ?>