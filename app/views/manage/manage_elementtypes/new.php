<?php 
/**
 * manage_elementtypes new view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage manage
 */
if(isset($form)){
  $this->renderElement('form', array('form'=>$form));
} else {
  print 'There are no new ApplyElement classes available';
}
?>