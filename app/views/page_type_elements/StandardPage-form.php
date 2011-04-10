<?php 
/**
 * StandardPage Form Element
 * @package jazzee
 * @subpackage apply
 */
?>
<div id='leadingText'><?php print $page->leadingText?></div>
<?php 
  $form = $page->getForm();
  $form->action = $action;
  $this->renderElement('form', array('form'=> $form));
?>
<div id='trailingText'><?php print $page->trailingText?></div>