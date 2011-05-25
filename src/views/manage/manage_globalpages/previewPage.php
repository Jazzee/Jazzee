<?php 
/**
 * manage_globalpages previewPage view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
if($page){
  print "<div id='leadingText'>{$page->leadingText}</div>";
  $form = $page->getForm();
  if($form){
    $this->renderElement('form', array('form'=> $form));
  }
  print "<div id='trailingText'>{$page->trailingText}</div>";
}
?>