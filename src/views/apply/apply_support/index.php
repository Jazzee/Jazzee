<?php
/**
 * apply_support view
 *
 */
$today = new DateTime;
?>
<h1>Applicant Messages and Support</h1>
<?php
if ($count = $applicant->unreadMessageCount()) { ?>
  <p><strong>You have <?php print $count; ?> unread messages.</strong></p>
<?php
}?>
<a href='<?php print $this->controller->applyPath('support/new'); ?>'>Create a new message</a>

<?php
if (count($threads)) { ?>
  <div class='discussion'>
    <table>
      <thead><tr><th>Message</th><th>Last Message</th><th>Messages</th></tr></thead>
      <tbody>
  <?php
  foreach ($threads as $thread) { ?>
          <tr class='<?php
    if ($thread->hasUnreadMessage(\Jazzee\Entity\Message::PROGRAM)) {
      print 'unread';
    } else {
      print 'read';
    }
    ?>'>
            <td><strong><?php print $thread->getSubject(); ?></strong>
              <br /><a href='<?php print $this->controller->applyPath('support/single/' . $thread->getId()); ?>'><?php print strip_tags(substr($thread->getLastMessage()->getText(), 0, 100)); ?>
              <?php
              if (strlen($thread->getLastMessage()->getText()) > 100) {
                print '...';
              }
              ?>
              </a>
            </td>
            <td>
              <?php
              if ($thread->getLastMessage()->getSender() == \Jazzee\Entity\Message::APPLICANT) { ?>
                From you
                <?php
              } else {?>
                From your program
              <?php
              }
              if ($thread->getCreatedAt()->diff($today)->days > 0) {
                print $thread->getCreatedAt()->diff($today)->days . ' days ago';
              } else {
                print 'less than one day ago';
              }?>
            </td>
            <td>
    <?php
    print $thread->getMessageCount() . ' total and ';
    print $thread->getUnreadMessageCount(\Jazzee\Entity\Message::PROGRAM) . ' new messages';
    ?>
            </td>
          </tr>
  <?php
  } //end foreach threads   ?>
      </tbody>
    </table>
  </div>
  <?php
} //enf if $threads