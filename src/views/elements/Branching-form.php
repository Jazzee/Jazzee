<?php 
/**
 * Branching Page Form Element
 * @package jazzee
 * @subpackage apply
 */
if($page->getJazzeePage()->getForm()->getElementByName('level')->getValue() > 1) { ?>
  <p><a href='<?php print $this->controller->getActionPath() ?>'>Choose a different option</a></p>
<?php } ?>

<div id='leadingText'><?php print $page->getLeadingText()?></div>
<?php $this->renderElement('form', array('form'=> $page->getJazzeePage()->getForm())); ?>
<div id='trailingText'><?php print $page->getTrailingText()?></div>