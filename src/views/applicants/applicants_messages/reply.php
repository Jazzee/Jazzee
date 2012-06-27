<?php
/**
 * applicants_messages reply view
 */
$count = 0;
?>
<h1>Reply to: <?php print $thread->getSubject(); ?></h1>
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
      <?php $count++; ?>
  <?php
  } ?>
  <?php
  for ($i = 0; $i < $count; $i++) {?>
    </div>
  <?php
  }?>
  </div>
<?php $this->renderElement('form', array('form' => $form));