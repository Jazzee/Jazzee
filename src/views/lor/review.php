<?php 
/**
 * lor review view
 * Review the information submitted
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage lor
 */
?>
<h3>Thank you.  We have received your recommendation.</h3>
<h5>For our applicant's security this page will only display once.  If you wish to have a copy of this recommendation for your records you should print one now.</h5>
<?php
$class = $page->getType()->getClass();
$this->renderElement($class::lorReviewElement(), array('page'=>$page, 'answer'=>$answer));