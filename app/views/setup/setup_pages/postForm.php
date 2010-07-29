<?php 
/**
 * setup_pages postForm view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
if(isset($form)){
  $this->renderElement('jsonForm', array('form'=>$form));
}
?>