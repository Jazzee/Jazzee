<?php 
/**
 * lor Standard page type view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage lor
 */
?>
<div id='leadingText'><?php print $page->getLeadingText()?></div>
  <?php
    $jp = $page->getApplicationPageJazzeePage();
    $jp->setController($this->controller);
    $this->renderElement('form', array('form'=> $jp->getForm()));
  ?>
<div id='trailingText'><?php print $page->getTrailingText()?></div>
  