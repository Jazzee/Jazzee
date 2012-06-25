<?php

/**
 * manage_pagetypes new view
 *
 */
if (isset($form)) {
  $this->renderElement('form', array('form' => $form));
} else {
  print 'There are no new ApplyPage classes available';
}