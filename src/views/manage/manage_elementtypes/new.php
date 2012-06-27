<?php

/**
 * manage_elementtypes new view
 *
 */
if (isset($form)) {
  $this->renderElement('form', array('form' => $form));
} else {
  print 'There are no new ApplyElement classes available';
}