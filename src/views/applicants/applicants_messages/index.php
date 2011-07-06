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
        foreach($threads as $message){?>
          <tr>
            <td class='<?php 
              if($message->isReadThread(\Jazzee\Entity\Message::PROGRAM)) print 'read';
              else print 'unread';
            ?>'>
            </td>
            <td><?php print $message->getLastMessage()->getCreatedAt()->format('l F jS Y \a\t g:ia')?> by  by <?php print $message->getApplicant()->getFullName();?></td>
            <td><a href='<?php print $this->path('applicants/messages/single/' . $message->getId());?>'><?php print $message->getLastMessage()->getSubject();?></a></td> 
          </tr>
        <?php } //end foreach threads ?>
      </tbody>
    </table>
  </div>
<?php } //enf if $threads ?>
<p><a href='<?php print $this->path('applicants/messages/all');?>'>All Messages</a></p>