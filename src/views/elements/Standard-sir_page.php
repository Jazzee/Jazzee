<?php
/**
 * sir_status_page Standard page type view
 */
?>
<div id='leadingText'><?php print $applicationPage->getLeadingText() ?></div>
<?php
$form = $applicationPage->getJazzeePage()->getForm();
$form->setAction($actionPath);
$form->newHiddenElement('confirm', $confirm);
$this->renderElement('form', array('form' => $form));
?>
<div id='trailingText'><?php print $applicationPage->getTrailingText() ?></div>