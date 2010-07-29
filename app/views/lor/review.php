<?php 
/**
 * lor review view
 * Review the information submitted
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage lor
 */
?>
<h3>Thank you.  We have recieved your recommendation.</h3>
<p>For our applicant's security this page will only display once.  If you wish to have a copy of this recommendation for your records you should make one now.</p>
<?php
if($answer){
  $allAnswersByElementId = array();
  foreach($answer->Elements as $answerElement){
    $allAnswersByElementId[$answerElement->elementID][] = $answerElement;
  }
  foreach($answer->Page->Elements as $e){
    $element = new $e->ElementType->class($e);
    if(!empty($allAnswersByElementId[$e->id])) $element->setValueFromAnswer($allAnswersByElementId[$e->id]);
    $value = $element->displayValue();
    if($value) print "<p><strong>{$e->title}:</strong>&nbsp;" . $value . '</p>'; 
  }
}
?>