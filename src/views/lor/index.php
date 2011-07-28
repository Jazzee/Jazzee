<?php 
/**
 * lor index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage lor
 */
?>
<h4>Recommendation for: <?php print $applicantName; ?></h4>
<h5>Deadline: <?php print $deadline; ?></h5>
<div id='leadingText'><?php print $page->getLeadingText() ?></div>
<?php $this->renderElement('form', array('form'=> $form));?>
<div id='trailingText'><?php print $page->getTrailingText() ?></div>