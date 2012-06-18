<?php
namespace Jazzee\Interfaces;
/**
 * XML Page interface
 * Pages which implement this can generate XML
 * 
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage pages
 */
interface XmlPage 
{ 
  /**
   * Get the current answers as an xml element
   * @param \DOMDocument $dom
   * @return array DOMElement
   */
  function getXmlAnswers(\DOMDocument $dom);
}