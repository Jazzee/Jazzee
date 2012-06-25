<?php

/**
 * applicants_download index view
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
if (isset($outputType)) {
  switch ($outputType) {
    case 'string':
      header("Content-type: {$type}");
      header("Content-Disposition: attachment; filename={$filename}");
      ob_end_clean();
      print $string;
      exit(0);
    case 'xml':
      echo $xml->saveXML();
  }
} else {
  $this->renderElement('form', array('form' => $form));
}