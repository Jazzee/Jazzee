<?php 
/**
 * lor index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage lor
 */

print "<div id='leadingText'>{$page->leadingText}</div>";
if($form){
  $this->renderElement('form', array('form'=> $form));
}
print "<div id='trailingText'>{$page->trailingText}</div>";
?>