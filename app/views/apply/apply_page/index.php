<?php 
/**
 * apply_page view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
if($answers = $page->getAnswers()){
  print "<div id='answers'>";
  foreach($answers as $answer){
    print "<div class='answer";
    if($currentAnswerID == $answer->getID()){
      print ' active';
    }
    print "'><h5>Saved Answer</h5>";
    foreach($answer->getElements() as $id => $title){
      $value = $answer->getDisplayValueForElement($id);
      if($value){
        print "<p><strong>{$title}:</strong>&nbsp;" . $value . '</p>'; 
      }
    }
    print "<p class='status'>";
    foreach($answer->applyStatus() as $title => $value){
      print "{$title}: {$value} <br />"; 
    }
    print '</p>';
    print "<p class='controls'>";
    $basePath = "apply/{$page->Application->Program->shortName}/{$page->Application->Cycle->name}/page/{$page->id}";
    if($currentAnswerID == $answer->getID()){
      print '<a class="undo" href="' . $this->path($basePath) . '">Undo</a>';
    } else {
      foreach($answer->applyTools($basePath) as $name => $path){
        print "<a class='{$name}' href='" . $this->path($path) . "'>{$name}</a>";
      }
    }
    print '</p>';
    
    print "</div>";
  }
  print '</div>';
}
print "<div id='leadingText'>{$page->leadingText}</div>";
if($form){
  $this->renderElement('form', array('form'=> $form));
}
print "<div id='trailingText'>{$page->trailingText}</div>";
?>