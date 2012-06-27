<?php
/**
 * apply_status admitLetter view
 */
if ($applicant->isLocked() and $applicant->getDecision()->getFinalAdmit()) {
  print $text;
}