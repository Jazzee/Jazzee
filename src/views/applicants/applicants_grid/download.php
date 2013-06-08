<?php

/**
 * applicants_download index view
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
if (isset($outputType)) {
  setcookie('fileDownload', 'complete', 0, '/');
  switch ($outputType) {
    case 'string':
      header("Content-type: {$type}");
      header("Content-Disposition: attachment; filename={$filename}");
      ob_end_clean();
      print $string;
      exit(0);
    case 'file':
      header("Content-type: {$type}");
      header("Content-Disposition: attachment; filename={$filename}");
      header('Content-Length: ' . filesize($filePath));
      header('Content-Transfer-Encoding: binary');
      ob_end_clean();
      print file_get_contents($filePath);
      unlink($filePath);
      exit(0);
    case 'xml':
      echo $xml->saveXML();
      break;
    case 'json':
      header("Content-type: application/json");
      header("Content-Disposition: attachment; filename={$filename}");
      ob_end_clean();
      print json_encode($output, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
      exit(0);
  }
} else {
  $this->renderElement('form', array('form' => $form));
}