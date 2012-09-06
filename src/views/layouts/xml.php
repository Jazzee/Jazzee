<?php

/**
 * XML layout
 *
 */
header("Content-type: application/xml; charset=UTF-8");
header('Content-Disposition: attachment; filename=' . $filename);
header('X-FRAME-OPTIONS: SAMEORIGIN');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
$xml = new DOMDocument('1.0', 'UTF-8');
$xml->formatOutput = true;

$response = $xml->createElement("response");
$xml->appendChild($response);
$response->appendChild($xml->createElement('status', $status));
$mxml = $xml->createElement("messages");
$messages = $this->controller->getMessages();
foreach ($messages as $message) {
  $m = $xml->createElement('message', $message['text']);
  $m->setAttribute('type', $message['type']);
  $mxml->appendChild($m);
}
$response->appendChild($mxml);

if (!empty($layoutContent)) {
  $body = new DOMDocument('1.0', 'UTF-8');
  $body->preserveWhiteSpace = true;
  $body->loadXML($layoutContent);
  $bodyNode = $xml->importNode($body->documentElement, true);
  $response->appendChild($bodyNode);
}
echo $xml->saveXML();