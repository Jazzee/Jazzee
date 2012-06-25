<?php
/**
 * applicants_view generic form
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
if (isset($result)) {
  ?>
  "result":<?php print json_encode($result) . ',';
}
if (isset($form)) {
  $this->renderElement('jsonForm', array('form' => $form));
}