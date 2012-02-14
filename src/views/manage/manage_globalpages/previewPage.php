<?php 
/**
 * manage_globalpages previewPage view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
$class = $page->getPage()->getType()->getClass();
$this->renderElement($class::APPLY_PAGE_ELEMENT, array('page'=>$page, 'currentAnswerID'=>false, 'applicant'=>$applicant));
?>