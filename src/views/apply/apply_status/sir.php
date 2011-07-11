<?php 
/**
 * apply_status view
 * @package jazzee
 * @subpackage apply
 */
if($applicant->isLocked() and $applicant->getDecision()->status() == 'finalAdmit'){
  $this->renderElement('form', array('form'=> $form));
}