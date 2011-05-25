<?php 
/**
 * apply_status view
 * @package jazzee
 * @subpackage apply
 */
if($status['admit']){
  if(!$status['accept'] and !$status['decline']){
    $this->renderElement('form', array('form'=> $form));
  } else {
    print '<p>You have already registered your enrollment decision';   
  }
} else {
  print '<p>You have not been admitted</p>';
}