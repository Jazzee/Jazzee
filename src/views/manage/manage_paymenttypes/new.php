<?php

/**
 * manage_paymenttypes new view
 *
 */
if (isset($form)) {
  $this->renderElement('form', array('form' => $form));
} else {
  print 'There are no new ApplyPayment classes available';
}