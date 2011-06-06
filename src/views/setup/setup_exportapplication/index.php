<?php
/**
 * Export application data to xml
 */
$xml = new DOMDocument();
$xml->formatOutput = true;

$app = $xml->createElement("application");

$preferences = $xml->createElement("preferences");
$preferences->appendChild($xml->createElement('contactName', $application->getContactName()));
$preferences->appendChild($xml->createElement('contactEmail', $application->getContactEmail()));
$preferences->appendChild($xml->createElement('welcome', $application->getWelcome()));
$preferences->appendChild($xml->createElement('open', $application->getOpen()->format('c')));
$preferences->appendChild($xml->createElement('close', $application->getClose()->format('c')));
$preferences->appendChild($xml->createElement('begin', $application->getBegin()->format('c')));
$preferences->appendChild($xml->createElement('admitletter', $application->getAdmitLetter()));
$preferences->appendChild($xml->createElement('denyletter', $application->getDenyLetter()));
$preferences->appendChild($xml->createElement('statuspagetext', $application->getStatusPageText()));

$app->appendChild($preferences);

$pages = $xml->createElement("pages");
foreach($application->getPages() as $page){
  $pages->appendChild($this->controller->pageXml($xml, $page));
}
$app->appendChild($pages);
$xml->appendChild($app);
echo $xml->saveXML();