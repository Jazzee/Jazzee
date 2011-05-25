<?php 
/**
 * applicants_communication index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */

if(empty($threads)):?>
  <p>No Messages</p>
<?php else: ?>
  <?php foreach($threads as $thread): ?>
  	<div class='threaded'>
		  <?php 
		  $count = 0;
		  do{
		    print '<div>';
		    print "<p>At {$thread->createdAt} ";
		    if($thread->sentBy == 'user'){
		      if($thread->User->id == $user->id){
		        print 'you ';
		      } else {
		       print "{$thread->User->firstName} {$thread->User->lastName} ";
		      }
		    } else {
		      print "{$thread->Applicant->firstName} {$thread->Applicant->lastName} ";
		    }
		    print "said</p><p>{$thread->text}</p>";
		    $id = $thread->id;
		    $thread = $thread->Reply;
		    $count++;
		  } while ($thread->exists());
		  print '<a href="' . $this->controller->path("applicants/communication/reply/{$id}") . '" class="reply">reply</a>';
		  //close up all the divs
		  for($i=0;$i<$count;$i++) print '</div>';
		  ?>
		</div>
  <?php endforeach; ?>
<?php endif; ?>