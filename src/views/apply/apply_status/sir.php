<?php 
/**
 * apply_status view
 * @package jazzee
 * @subpackage apply
 */
if($applicant->isLocked() and $applicant->getDecision()->status() == 'finalAdmit'){
  if(isset($confirm)){
    $applicationPage = $confirm?$sirAcceptPage:$sirDeclinePage;
    $class = $applicationPage->getPage()->getType()->getClass();
    $this->renderElement($class::sirPageElement(), array('confirm'=>$confirm, 'applicationPage'=>$applicationPage));
  } else {
    $this->renderElement('form', array('form'=> $form));
  }
}