<?php 
/**
 * applicants_messages index view
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
?>

<p><a href='<?php print $this->path('applicants/messages/new');?>'>New Message</a></p>

<?php if(count($threads)){ ?>
  <h1>Unread Messages</h1>
  <div class='discussion'>
    <table>
      <thead><tr><th></th><th>Sent</th><th>Subject</th></tr></thead>
      <tbody>
        <?php 
        foreach($threads as $thread){?>
          <tr>
            <td class='<?php 
              if($thread->hasUnreadMessage(\Jazzee\Entity\Message::APPLICANT)) print 'unread';
              else print 'read';
            ?>'>
            </td>
            <td><?php print $thread->getCreatedAt()->format('l F jS Y \a\t g:ia')?> by <?php print $thread->getApplicant()->getFullName();?></td>
            <td><a href='<?php print $this->path('applicants/messages/single/' . $thread->getId());?>'><?php print $thread->getSubject();?></a></td> 
          </tr>
        <?php } //end foreach threads ?>
      </tbody>
    </table>
  </div>
<?php } //enf if $threads ?>
<p><a href='<?php print $this->path('applicants/messages/all');?>'>All Messages</a></p>