<?php 
/**
 * apply_status denyLetter iew
 * @package jazzee
 * @subpackage apply
 */
if($applicant->isLocked() and $applicant->getDecision()->getFinalDeny()){
  print $text;
}
?>