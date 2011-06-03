<?php 
/**
 * Recommenders Answer Element
 * @package jazzee
 * @subpackage apply
 */
?>
<div class='answer<?php if($currentAnswerID and $currentAnswerID == $answer->getID()) print ' active'; ?>'>
  <h5>Saved Recommenders</h5>
  <?php 
  foreach($answer->getPage()->getElements() as $element){
    $value = $element->getJazzeeElement()->displayValue($answer);
    if($value){
      print '<p><strong>' . $element->getTitle() . ':</strong>&nbsp;' . $value . '</p>'; 
    }
  }
  ?>
  <p class='status'>
    Last Updated: <?php print $answer->getUpdatedAt()->format('M d Y g:i a');?><br />
    <?php if($child = $answer->getChildren()->first()){?>
      <br />Status: This recommendation was received on <?php print $child->getLastUpdatedAt('l F jS Y g:ia');
    } else if($answer->isLocked()){?>
      Invitation Sent: <?php print $answer->getUpdatedAt()->format('l F jS Y g:ia'); ?><br />
      Status: You cannot make changes to this recommendation becuase the invitation has already been sent.
      <?php if($answer->getUpdatedAt()->diff(new DateTime('now'))->days < \Jazzee\Entity\Page\Recommenders::RECOMMENDATION_EMAIL_WAIT_DAYS){?> 
        You will be able to send the invitation to your recommender again in <?php print (\Jazzee\Entity\Page\Recommenders::RECOMMENDATION_EMAIL_WAIT_DAYS - $answer->getUpdatedAt()->diff(new DateTime('now'))->days);?> days.
      <?php }?>
    <?php }?>
  </p>
  <p class='controls'>
    <?php 
    if(!$answer->isLocked() and $currentAnswerID and $currentAnswerID == $answer->getID()){?>
      <a class='undo' href='<?php print $this->controller->getActionPath() ?>'>Undo</a>
    <?php } else if(!$answer->isLocked()){ ?>
      <a class='edit' href='<?php print $this->controller->getActionPath();?>/edit/<?php print $answer->getId()?>'>Edit</a>
      <a class='delete' href='<?php print $this->controller->getActionPath();?>/delete/<?php print $answer->getId()?>'>Delete</a>
      <a class='invite' href='<?php print $this->controller->getActionPath();?>/do/sendEmail/<?php print $answer->getId()?>'>Send Invitation Email</a>
    <?php } else if($answer->getUpdatedAt()->diff(new DateTime('now'))->days >= \Jazzee\Entity\Page\Recommenders::RECOMMENDATION_EMAIL_WAIT_DAYS){ ?>
      <a class='invite' href='<?php print $this->controller->getActionPath();?>/do/sendReminder/<?php print $answer->getId()?>'>Send Reminder Email</a>
    <?php }?>
  </p>
</div>