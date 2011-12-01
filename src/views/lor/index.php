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
<p>This recommendation was requested from 
<?php print $page->getParent()->getElementByFixedId(\Jazzee\Entity\Page\Recommenders::FID_FIRST_NAME)->getJazzeeElement()->displayValue($answer);?>
<?php print $page->getParent()->getElementByFixedId(\Jazzee\Entity\Page\Recommenders::FID_LAST_NAME)->getJazzeeElement()->displayValue($answer);?>.
If this is not you, please do not complete this recommendation</p>
<p><?php print $applicantName . ' <em>' . ($page->getParent()->getElementByFixedId(\Jazzee\Entity\Page\Recommenders::FID_WAIVE_RIGHT)->getJazzeeElement()->displayValue($answer)=='Yes'?'has':'has not') . '</em>';?> waived their right to view this letter after the application is reviewed.</p>
<div id='leadingText'><?php print $page->getLeadingText() ?></div>
<?php $this->renderElement('form', array('form'=> $form));?>
<div id='trailingText'><?php print $page->getTrailingText() ?></div>