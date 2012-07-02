<?php
/**
 * apply_page Lock page type view
 */
?>
<div id='leadingText'><?php print $page->getLeadingText() ?></div>
<?php $this->renderElement('form', array('form' => $page->getJazzeePage()->getForm())); ?>
<div id='trailingText'><?php print $page->getTrailingText() ?></div>