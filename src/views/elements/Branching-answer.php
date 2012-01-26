<?php 
/**
 * StandardPage Answer Element
 * @package jazzee
 * @subpackage apply
 */
?>
<div class='answer<?php if($currentAnswerID and $currentAnswerID == $answer->getID()) print ' active'; ?>'>
  <h5>Saved Answer</h5>
  <?php 
  $child = $answer->getChildren()->first();
  print '<p><strong>' . $page->getPage()->getVar('branchingElementLabel') . ':</strong>&nbsp' . $child->getPage()->getTitle() . '</p>';
  
  foreach($child->getPage()->getElements() as $element){
    $element->getJazzeeElement()->setController($this->controller);
    $value = $element->getJazzeeElement()->displayValue($child);
    if($value){
      print '<p><strong>' . $element->getTitle() . ':</strong>&nbsp;' . $value . '</p>'; 
    }
  }
  ?>
  <p class='status'>
    Last Updated: <?php print $answer->getUpdatedAt()->format('M d Y g:i a');?>
    <?php if($answer->getPublicStatus()){?><br />Status: <?php print $answer->getPublicStatus()->getName();}?>
  </p>
  <p class='controls'>
    <?php 
    if($currentAnswerID and $currentAnswerID == $answer->getID()){?>
      <a class='undo' href='<?php print $this->controller->getActionPath() ?>'>Undo</a>
    <?php } else { ?>
      <a class='edit' href='<?php print $this->controller->getActionPath();?>/edit/<?php print $answer->getId()?>'>Edit</a>
      <a class='delete' href='<?php print $this->controller->getActionPath();?>/delete/<?php print $answer->getId()?>'>Delete</a>
    <?php } ?>
  </p>
</div>