<?php 
/**
 * applicants_view extendDeadline view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
if(isset($form)){
  $this->renderElement('jsonForm', array('form'=>$form));
}
?>