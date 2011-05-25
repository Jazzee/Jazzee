<?php 
/**
 * manage_paymenttypes new view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
if(isset($form)){
  $this->renderElement('form', array('form'=>$form));
} else {
  print 'There are no new ApplyPayment classes available';
}
?>