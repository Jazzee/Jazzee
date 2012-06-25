<?php
/**
 * lor Standard page type view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage lor
 */
?>
<div id='leadingText'><?php print $page->getLeadingText() ?></div>
  <?php
  $jp = $page->getApplicationPageJazzeePage();
  $jp->setController($this->controller);
  $form = $jp->getForm();
  $element = $form->getElementByName('submit');
  $element->setValue('Submit Recommendation (Cannot be undone)');
  $this->renderElement('form', array('form' => $form));
  ?>
<div id='trailingText'><?php print $page->getTrailingText() ?></div>
