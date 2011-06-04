<?php
/**
 * XML layout
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
header("Content-type: application/xml");
$xml = new DOMDocument();
$xml->formatOutput = true;

$response = $xml->createElement("response");
$xml->appendChild($response);
$response->appendChild($xml->createElement('status', $status));
$mxml = $xml->createElement("messages");
$messages = $this->controller->getMessages();
foreach($messages as $message){
  $m = $xml->createElement('message', $message['text']);
  $m->setAttribute('type', $message['type']);
  $mxml->appendChild($m);
}
$response->appendChild($mxml);

$body = new DOMDocument;
$body->preserveWhiteSpace = true;
$body->loadXML($layoutContent);
$bodyNode = $xml->importNode($body->documentElement, true);
$response->appendChild($bodyNode);

echo $xml->saveXML();