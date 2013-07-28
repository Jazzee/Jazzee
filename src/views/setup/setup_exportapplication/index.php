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
$preferences->appendChild($this->controller->createCdataElement($xml, 'welcome', $application->getWelcome()));
$preferences->appendChild($xml->createElement('open', ($application->getOpen()?$application->getOpen()->format('c'):null)));
$preferences->appendChild($xml->createElement('close', ($application->getClose()?$application->getClose()->format('c'):null)));
$preferences->appendChild($xml->createElement('begin', ($application->getBegin()?$application->getBegin()->format('c'):null)));
$preferences->appendChild($this->controller->createCdataElement($xml, 'admitletter', $application->getAdmitLetter()));
$preferences->appendChild($this->controller->createCdataElement($xml, 'denyletter', $application->getDenyLetter()));
$preferences->appendChild($this->controller->createCdataElement($xml, 'statusIncompleteText', $application->getStatusIncompleteText()));
$preferences->appendChild($this->controller->createCdataElement($xml, 'statusNoDecisionText', $application->getStatusNoDecisionText()));
$preferences->appendChild($this->controller->createCdataElement($xml, 'statusAdmitText', $application->getStatusAdmitText()));
$preferences->appendChild($this->controller->createCdataElement($xml, 'statusDenyText', $application->getStatusDenyText()));
$preferences->appendChild($this->controller->createCdataElement($xml, 'statusAcceptText', $application->getStatusAcceptText()));
$preferences->appendChild($this->controller->createCdataElement($xml, 'statusDeclineText', $application->getStatusDeclineText()));
$preferences->appendChild($xml->createElement('visible', $application->isVisible()?'1':'0'));
$preferences->appendChild($xml->createElement('byinvitationonly', $application->isByInvitationOnly()?'1':'0'));
$preferences->appendChild($this->controller->createCdataElement($xml, 'externalIdValidationExpression', $application->getExternalIdValidationExpression()));

$app->appendChild($preferences);

$applicationPages = $xml->createElement("pages");
$pages = $application->getApplicationPages();
foreach ($pages as $page) {
  $applicationPages->appendChild($this->controller->pageXml($xml, $page));
}
$app->appendChild($applicationPages);
$xml->appendChild($app);
echo $xml->saveXML();