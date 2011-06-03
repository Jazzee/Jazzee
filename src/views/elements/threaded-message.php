<?php
/**
 * Recursive element for threaded message
 */
?>
<div>
  <p class='header'>On <?php print $date ?><?php print $sender ?> said</p>
  <p><?php print $text;?></p>
  <p class='footer'><a href='<?php print $replyLink;?>'>Reply</a></p>
  <?php 
    foreach($replies as $message){
      $this->renderElement('threaded-message', array('date'=>$message['date'], 'sender'=>$message['sender'], 'text'=>$message['text'],'replyLink'=>$message['replyLink'], 'replies'=>$message['replies']));
    }
  ?>
</div>