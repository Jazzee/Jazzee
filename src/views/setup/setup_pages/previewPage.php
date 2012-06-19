<?php 
/**
 * setup_pages previewPage view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
$class = $page->getPage()->getType()->getClass();
$this->renderElement($class::applyPageElement(), array('page'=>$page, 'currentAnswerID'=>false, 'applicant'=>$applicant));
?>