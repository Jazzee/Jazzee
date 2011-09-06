<?php 
/**
 * apply_support view
 * @package jazzee
 * @subpackage apply
 */
?>
<h1>Applicant Support</h1>
<a href='support/new'>New Message</a>

<?php if(count($threads)){ ?>
  <div class='discussion'>
    <h3>Your Messages</h3>
    <table>
      <thead><tr><th></th><th>Sent</th><th>Subject</th><th>Reply</th></tr></thead>
      <tbody>
        <?php 
        foreach($threads as $thread){?>
          <tr>
            <td class='<?php
              if($thread->hasUnreadMessage(\Jazzee\Entity\Message::PROGRAM)) print 'unread';
              else print 'read';
            ?>'>
            </td>
            <td><?php print $thread->getCreatedAt()->format('l F jS Y \a\t g:ia')?></td>
            <td><a href='<?php print $this->path($basePath . '/support/single/' . $thread->getId());?>'><?php print $thread->getSubject();?></a></td> 
            <td><a href='<?php print $this->path($basePath . '/support/reply/' . $thread->getId());?>'>Reply</a></td> 
          </tr>
        <?php } //end foreach threads ?>
      </tbody>
    </table>
  </div>
<?php } //enf if $threads ?>

