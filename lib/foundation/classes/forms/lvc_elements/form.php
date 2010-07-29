<?php
/**
 * Form layout
 * Sets the structure for the element and the individual controls and displays fill in data
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
 ?><div class='form'>
  <p class='required'>indicates a required field</p>
  <form<?
    foreach($form->getAttributes() as $memberName => $htmlName){
      $this->renderElement('attribute', array('name'=>$htmlName, 'value'=>$form->$memberName));
    }
    ?>>
  <?php
  foreach($form->getFields() as $field){
    $this->renderElement('field', array('field'=>$field));
  }
  ?>
  </form>
</div>