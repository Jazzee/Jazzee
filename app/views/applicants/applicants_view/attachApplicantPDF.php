<?php 
/**
 * applicants_view attachAapplicantPDF view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
if(isset($form)){
  $this->renderElement('jsonForm', array('form'=>$form));
}
?>