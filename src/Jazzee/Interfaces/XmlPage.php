<?php
namespace Jazzee\Interfaces;

/**
 * XML Page interface
 * Pages which implement this can generate XML
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
interface XmlPage extends DataPage
{

  /**
   * Get the current answers as an xml element
   * @param \DOMDocument $dom
   * @param integer $version the XML version to generate
   * @return array DOMElement
   */
  function getXmlAnswers(\DOMDocument $dom, $version);
}