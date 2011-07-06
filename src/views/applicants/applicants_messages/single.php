<?php
/**
 * applicants_messages reply view
 */
$lastMessage = $message->getLastMessage();
$message = $message->getFirstMessage();
$count = 0;
?>
<a href='<?php print $this->path('applicants/messages');?>'>Unread Messages</a>
<h1>Message</h1>
<div class='threaded'>
  <?php do{ ?>
    <div class='<?php print ($message->isRead(\Jazzee\Entity\Message::PROGRAM)?'read':'unread'); ?>'>
      <h4><?php print $message->getSubject();?></h4>
      <p class='header'>On <?php print $message->getCreatedAt()->format('l F jS Y \a\t g:ia') ?> 
      <?php if($message->getSender() == \Jazzee\Entity\Message::APPLICANT){
        print $message->getApplicant()->getFullName();
        $message->read();
        $this->controller->getEntityManager()->persist($message);
      } else {
        print 'your program';
      } ?> said</p>
      <p><?php print $message->getText();?></p>
      <p class='footer'>
          <a href='<?php print $this->path('applicants/messages/' . '/markUnread/' .$message->getId());?>'>Mark as Unread</a>
      </p>
  <?php $count++; ?>
  <?php } while ($message = $message->getReply());?>
  <?php for($i = 0; $i<$count; $i++) print '</div>';?>
</div>
<a href='<?php print $this->path('applicants/messages/reply/' . $lastMessage->getId());?>'>Reply</a>