<?php
namespace Jazzee\Display;

/**
 * Union display takes other displays which may limit applicant data and
 * returns the maximum union of these displays
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Union implements \Jazzee\Interfaces\Display
{ 
  protected $_pageIds;
  protected $_elementIds;
  protected $_elements;
  protected $_displays;

  /**
   * Setup arrays
   */
  public function __construct()
  {
    $this->_displays = array();
    $this->calculateUntion();
  }
  
  /**
   * Add a display to our set
   * @param \Jazzee\Interfaces\Display $display
   */
  public function addDisplay(\Jazzee\Interfaces\Display $display){
    $this->_displays[$display->getId()] = $display;
    $this->calculateUntion();
  }
  
  
  /**
   * Calculate display union
   */
  protected function calculateUntion() {
    $this->_pageIds = array();
    $this->_elementIds = array();
    
    $elements = array();
    foreach($this->_displays as $display){
      $this->_pageIds = array_unique(array_merge($this->_pageIds, $display->getPageIds()));
      $this->_elementIds = array_unique(array_merge($this->_elementIds, $display->getElementIds()));
      foreach($display->listElements() as $element){
        $elements[$element->type.$element->name.$element->pageId] = $element;
      }
    }
    $this->_elements = array_values($elements);
  }

  /**
   * Get the name of the display
   * 
   * @return string
   */
  public function getName(){
    return 'Union Display';
  }

  /**
   * Get id
   * 
   * @return string
   */
  public function getId(){
    return 'union';
  }
  
  /**
   * Get an array of page ids that are shown by the display
   * 
   * @return array
   */
  public function getPageIds(){
    return $this->_pageIds;
  }
  
  /**
   * Get an array of elemnet IDs that are returned by the display
   * 
   * @return array
   */
  public function getElementIds(){
    return $this->_elementIds;
  }
  
  public function listElements()
  {
    return $this->_elements;
  }
  
  /**
   * Should a page be displayed
   * 
   * @param \Jazzee\Entity\Page $page
   * 
   * @return boolean
   */
  public function displayPage(\Jazzee\Entity\Page $page){
    return in_array($page->getId(), $this->_pageIds);
  }
  
  /**
   * Should an Element be displayed
   * @param \Jazzee\Entity\Element $element
   * 
   * @return boolean
   */
  public function displayElement(\Jazzee\Entity\Element $element){
    return in_array($element->getId(), $this->_elementIds);
  }

  public function hasDisplayElement(Element $displayElement)
  {
    foreach($this->listElements() as $element){
      if($displayElement->sameAs($element)){
        return true;
      }
    }

    return false;
  }
}