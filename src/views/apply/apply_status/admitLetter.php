<?php 
/**
 * apply_status admitLetter iew
 * @package jazzee
 * @subpackage apply
 */
if($applicant->isLocked() and $applicant->getDecision()->getFinalAdmit()){
  print $text;
}
?>