<?php 
/**
 * apply_support view
 * @package jazzee
 * @subpackage apply
 */
?>
<h1>Applicant Support</h1>
<a href='support/new'>New Message</a>
<?php
if($messages->count()){ ?>
  <h3>Your Messages</h3>
  <?php foreach($messages as $message){
    if(!$message->getParent()){
    ?>
		<div class='threaded'>
		  <?php 
		  $replies = array($message);  //the first message goes in an array by itself
		  $count = 0;
		  do{
		    ?><div><?php
		    foreach($replies as $reply){?>
		      <p class='header'>On <?php print $reply->getCreatedAt()->format('l F jS Y \a\t g:ia') ?>
		        <?php if($reply->getSender() == 'applicant'){?>you <?php } else {?> your program <?php } ?> said</p>
		        <p><?php print $reply->getText();?></p>
		        <p class='footer'><a href='support/reply/<?php print $reply->getId();?>'>Reply</a></p>
		    <?php } //end foreach $replies
		    $replies = $reply->getReplies();
		    $count++;
		  } while (count($replies));
		  //close up all the divs
		  for($i=0;$i<$count;$i++) print '</div>';
		  ?>
		</div>
		<?php }?>
  <?php } ?>
<?php } ?>