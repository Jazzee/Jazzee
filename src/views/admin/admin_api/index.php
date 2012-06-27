<?php

/**
 * admin_api index view
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
if ($xml->hasChildNodes()) {
  $xml->formatOutput = true;
  echo $xml->saveXML();
}