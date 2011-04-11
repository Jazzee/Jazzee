<?php 
/**
 * StandardPage Form Element
 * @package jazzee
 * @subpackage apply
 */
?>
<div id='leadingText'><?php print $page->leadingText?></div>
<?php $this->renderElement('form', array('form'=> $form = $page->getForm())); ?>
<div id='trailingText'><?php print $page->trailingText?></div>