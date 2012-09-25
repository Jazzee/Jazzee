<?php
namespace Jazzee\Interfaces;

/**
 * XML Element interface
 * Elements which implement this can generate XML
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
interface XmlElement
{

  /**
   * Get the current answers as an xml element
   * @param \DOMDocument $dom
   * @param \Jazzee\Entity\Answer $answer
   * @param integer $version
   * @return array DOMElement
   */
  function getXmlAnswer(\DOMDocument $dom, \Jazzee\Entity\Answer $answer, $version);
}