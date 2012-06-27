<?php
/**
 * apply_support single view
 */
$count = 0;
?>
<a href='<?php print $this->controller->applyPath('support'); ?>'>All Messages</a>
<h1><?php print $thread->getSubject(); ?></h1>
<div class='threaded'>
  <?php
  foreach ($thread->getMessages() as $message) { ?>
    <div class='<?php print ($message->isRead(\Jazzee\Entity\Message::PROGRAM) ? 'read' : 'unread'); ?>'>
      <p class='header'>On <?php print $message->getCreatedAt()->format('l F jS Y \a\t g:ia') ?>
        <?php
        if ($message->getSender() == \Jazzee\Entity\Message::APPLICANT) {
          print 'you';
        } else {
          print 'your program';
          $message->read();
          $this->controller->getEntityManager()->persist($message);
        }
        ?> said</p>
      <p><?php print $message->getText(); ?></p>
      <p class='footer'>
        <?php
        if ($message->getSender() == \Jazzee\Entity\Message::PROGRAM) { ?>
          <a href='<?php print $this->controller->applyPath('support/markUnread/' . $message->getId()); ?>'>Mark as Unread</a>
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
  }?>
</div>
<a href='<?php print $this->controller->applyPath('support/reply/' . $thread->getId()); ?>'>Reply</a>