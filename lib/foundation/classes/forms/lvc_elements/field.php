<?php
/**
 * Form field layout
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
 ?><fieldset<?
foreach($field->getAttributes() as $memberName => $htmlName){
  $this->renderElement('attribute', array('name'=>$htmlName, 'value'=>$field->$memberName));
}
?>>
<?php 
if(!empty($field->legend)){
  echo "<legend>{$field->legend}</legend>\n";
}
if(!empty($field->instructions)){
  echo "<p class='instructions'>{$field->instructions}</p>\n";
}
?>
<?php 
foreach($field->getElements() as $element){
  $this->renderElement('element', array('element'=>$element));
}
?>
</fieldset>