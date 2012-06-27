<?php
/**
 * applicants_messages reply view
 */
$count = 0;
?>
<a href='<?php print $this->path('applicants/messages'); ?>'>Unread Messages</a><br />
<a href='<?php print $this->path('applicants/messages/all'); ?>'>All Messages</a>
<h1><?php print $thread->getSubject(); ?></h1>
<h3>Applicant: <a href='<?php print $this->controller->path('applicants/single/' . $thread->getApplicant()->getId()); ?>'><?php print $thread->getApplicant()->getFullName(); ?></a></h3>
<div class='threaded'>
  <?php
  foreach ($thread->getMessages() as $message) { ?>
    <div class='<?php print ($message->isRead(\Jazzee\Entity\Message::APPLICANT) ? 'read' : 'unread'); ?>'>
      <p class='header'>On <?php print $message->getCreatedAt()->format('l F jS Y \a\t g:ia') ?>
        <?php
        if ($message->getSender() == \Jazzee\Entity\Message::APPLICANT) {
          print $thread->getApplicant()->getFullName();
          $message->read();
          $this->controller->getEntityManager()->persist($message);
        } else {
          print 'your program';
        }
        ?> said</p>
      <p><?php print $message->getText(); ?></p>
      <p class='footer'>
        <?php
        if ($message->getSender() == \Jazzee\Entity\Message::APPLICANT) { ?>
          <a href='<?php print $this->path('applicants/messages/markUnread/' . $message->getId()); ?>'>Mark as Unread</a>
        <?php
        } ?>
      </p>
      <?php $count++; ?>
  <?php
  } ?>
  <?php
  for ($i = 0; $i < $count; $i++) {?>
    </div>
  <?php
  }  ?>
  </div>
  <a href='<?php print $this->path('applicants/messages/reply/' . $thread->getId()); ?>'>Reply</a>