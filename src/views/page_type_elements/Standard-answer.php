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
  foreach($answer->getPage()->getElements() as $element){
    $value = $element->getJazzeeElement()->displayValue($answer);
    if($value){
      print '<p><strong>' . $element->getTitle() . ':</strong>&nbsp;' . $value . '</p>'; 
    }
  }
  ?>
  <p class='status'>
  <?php
  foreach($answer->getJazzeeAnswer()->applyStatus() as $title => $value){
    print "{$title}: {$value} <br />"; 
  }
  ?>
  </p>
  <p class='controls'>
  <?php 
  if($currentAnswerID and $currentAnswerID == $answer->getID()){
    print '<a class="undo" href="' . $this->controller->getActionPath() . '">Undo</a>';
  } else {
    foreach($answer->getJazzeeAnswer()->applyTools() as $name => $path){
      print "<a class='{$name}' href='" . $this->controller->getActionPath() . $path . "'>{$name}</a>";
    }
  }
  ?>
  </p>
</div>