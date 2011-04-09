<?php 
/**
 * StandardPage Answer Element
 * @package jazzee
 * @subpackage apply
 */
?>
<div class='answer<?php if($currentAnswerID == $answer->getID()) print ' active'; ?>'>
  <h5>Saved Answer</h5>
  <?php 
  foreach($answer->getElements() as $id => $title){
    $value = $answer->getDisplayValueForElement($id);
    if($value){
      print "<p><strong>{$title}:</strong>&nbsp;" . $value . '</p>'; 
    }
  }
  ?>
  <p class='status'>
  <?php
  foreach($answer->applyStatus() as $title => $value){
    print "{$title}: {$value} <br />"; 
  }
  ?>
  </p>
  <p class='controls'>
  <?php 
  $basePath = "apply/{$page->Application->Program->shortName}/{$page->Application->Cycle->name}/page/{$page->id}";
  if($currentAnswerID == $answer->getID()){
    print '<a class="undo" href="' . $this->path($basePath) . '">Undo</a>';
  } else {
    foreach($answer->applyTools($basePath) as $name => $path){
      print "<a class='{$name}' href='" . $this->path($path) . "'>{$name}</a>";
    }
  }
  ?>
  </p>
</div>