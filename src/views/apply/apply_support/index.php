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
  <h3>Your Messages</h3>
  <div class='threaded'>
    <?php 
    foreach($threads as $message){
      $this->renderElement('threaded-message', array('date'=>$message['date'], 'sender'=>$message['sender'], 'text'=>$message['text'],'replyLink'=>$message['replyLink'], 'replies'=>$message['replies']));  
    } //end foreach threads ?>
  </div>
<?php } //enf if $threads ?>

