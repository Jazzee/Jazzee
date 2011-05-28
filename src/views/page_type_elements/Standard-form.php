<?php 
/**
 * StandardPage Form Element
 * @package jazzee
 * @subpackage apply
 */
?>
<div id='leadingText'><?php print $page->getLeadingText()?></div>
<?php $this->renderElement('form', array('form'=> $page->getJazzeePage()->getForm())); ?>
<div id='trailingText'><?php print $page->getTrailingText()?></div>