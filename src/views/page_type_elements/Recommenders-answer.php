<?php 
/**
 * Recommenders Answer Element
 * @package jazzee
 * @subpackage apply
 */
?>
<div class='answer<?php if($currentAnswerID and $currentAnswerID == $answer->getID()) print ' active'; ?>'>
  <h5>Recommender</h5>
  <?php 
  foreach($answer->getPage()->getElements() as $element){
    $element->getJazzeeElement()->setController($this->controller);
    $value = $element->getJazzeeElement()->displayValue($answer);
    if($value){
      print '<p><strong>' . $element->getTitle() . ':</strong>&nbsp;' . $value . '</p>'; 
    }
  }
  ?>
  <p class='status'>
    <strong>Last Updated:</strong> <?php print $answer->getUpdatedAt()->format('M d Y g:i a');?><br />
    <?php if($child = $answer->getChildren()->first()){?>
      <br /><strong>Status:</strong> This recommendation was received on <?php print $child->getUpdatedAt()->format('l F jS Y g:ia');
    } else if($answer->isLocked()){?>
      <strong>Invitation Sent:</strong> <?php print $answer->getUpdatedAt()->format('l F jS Y g:ia'); ?><br />
      <strong>Status:</strong> You cannot make changes to this recommendation becuase the invitation has already been sent.
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
    <?php } else if(!$answer->getChildren()->first() and $answer->getUpdatedAt()->diff(new DateTime('now'))->days >= \Jazzee\Entity\Page\Recommenders::RECOMMENDATION_EMAIL_WAIT_DAYS){ ?>
      <a class='invite' href='<?php print $this->controller->getActionPath();?>/do/sendEmail/<?php print $answer->getId()?>'>Send Reminder Email</a>
    <?php }?>
  </p>
</div>