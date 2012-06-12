<?php 
/**
 * sir_status_page Standard page type view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
?>
<div id='leadingText'><?php print $applicationPage->getLeadingText()?></div>
  <?php
    $form = $applicationPage->getJazzeePage()->getForm();
    $form->newHiddenElement('confirm', $confirm);
    $this->renderElement('form', array('form'=> $form));
  ?>
<div id='trailingText'><?php print $applicationPage->getTrailingText()?></div>