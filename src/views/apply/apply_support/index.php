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
      <thead><tr><th></th><th>Sent</th><th>Subject</th></tr></thead>
      <tbody>
        <?php 
        foreach($threads as $thread){?>
          <tr>
            <td class='<?php
              if($thread->isReadThread(\Jazzee\Entity\Message::APPLICANT)) print 'read';
              else print 'unread';
            ?>'>
            </td>
            <td><?php print $thread->getLastMessage()->getCreatedAt()->format('l F jS Y \a\t g:ia')?></td>
            <td><a href='<?php print $this->path($basePath . '/support/single/' . $thread->getId());?>'><?php print $thread->getLastMessage()->getSubject();?></a></td> 
          </tr>
        <?php } //end foreach threads ?>
      </tbody>
    </table>
  </div>
<?php } //enf if $threads ?>

