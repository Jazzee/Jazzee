<?php
/**
 * apply_status denyLetter view
 */
if ($applicant->isLocked() and $applicant->getDecision()->getFinalDeny()) {
  print $text;
}