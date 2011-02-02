<?php 
/**
 * apply_support view
 * @package jazzee
 * @subpackage apply
 */
$this->renderElement('form', array('form'=> $form));
$threads = $applicant->findCommunicationThreads();
if($threads->count() > 0): ?>
  <h3>Your Messages</h3>
  <?php foreach($threads as $thread):?>
		<div class='threaded'>
		  <?php 
		  $count = 0;
		  do{
		    print '<div>';
		    print "<p>At {$thread->createdAt} ";
		    if($thread->sentBy == 'applicant'){
		      print 'you ';
		    } else {
		      print "{$thread->User->firstName} {$thread->User->lastName} ";
		    }
		    print "said</p><p>{$thread->text}</p>";
		    $thread = $thread->Reply;
		    $count++;
		  } while ($thread->exists());
		  //close up all the divs
		  for($i=0;$i<$count;$i++) print '</div>';
		  ?>
		</div>
  <?php endforeach; ?>
<?php endif; ?>