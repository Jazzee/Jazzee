<?php 
/**
 * applicants_messages_list element
 * @package jazzee
 */
$today = new DateTime;
?>
<?php if(count($threads)){ ?>
  <div class='discussion'>
    <table>
      <thead><tr><th></th><th>Message</th><th>Last Message</th><th>Messages</th></tr></thead>
      <tbody>
        <?php 
        foreach($threads as $thread){?>
          <tr class='<?php 
              if($thread->hasUnreadMessage(\Jazzee\Entity\Message::APPLICANT)) print 'unread';
              else print 'read';
            ?>'>
            <td></td>
            <td><strong><?php print $thread->getSubject();?></strong>
              <br /><a href='<?php print $this->path('applicants/messages/single/' . $thread->getId());?>'><?php print strip_tags(substr($thread->getLastMessage(\Jazzee\Entity\Message::APPLICANT)->getText(), 0, 100));?>
              <?php if(strlen($thread->getLastMessage(\Jazzee\Entity\Message::APPLICANT)->getText()) > 100){ print '...'; }?>
              </a>
            </td>
            <td>
              <a href='<?php print $this->path('applicants/single/' . $thread->getApplicant()->getId());?>'><?php print $thread->getApplicant()->getFullName();?></a>
              <?php 
                if($thread->getCreatedAt()->diff($today)->days > 0){
                  print $thread->getCreatedAt()->diff($today)->days . ' days ago';
                } else {
                  print 'less than one day ago';
                }
              ?>
            </td>
            <td>
              <?php 
                  print $thread->getMessageCount(\Jazzee\Entity\Message::APPLICANT) . ' total and ';
                  print $thread->getUnreadMessageCount(\Jazzee\Entity\Message::APPLICANT) . ' new messages';
                ?>
            </td>
          </tr>
        <?php } //end foreach threads ?>
      </tbody>
    </table>
  </div>
<?php } //enf if $threads ?>