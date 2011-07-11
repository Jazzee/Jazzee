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

$applicationPages = $xml->createElement("pages");
$pages = $this->controller->getEntityManager()->getRepository('\Jazzee\Entity\ApplicationPage')->findBy(array('application'=>$application->getId()));
foreach($pages as $page){
  $applicationPages->appendChild($this->controller->pageXml($xml, $page));
}
$app->appendChild($applicationPages);
$xml->appendChild($app);
echo $xml->saveXML();