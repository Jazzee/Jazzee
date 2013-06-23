<?php
namespace Jazzee\Display;

/**
 * LImited display takes other displays which may limit applicant data and
 * returns the intersection of these displays
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Intersection implements \Jazzee\Interfaces\Display
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
    $this->calculateIntersection();
  }
  
  /**
   * Add a display to our set
   * @param \Jazzee\Interfaces\Display $display
   */
  public function addDisplay(\Jazzee\Interfaces\Display $display){
    $this->_displays[$display->getId()] = $display;
    $this->calculateIntersection();
  }

  /**
   * Calculate display intersection
   */
  protected function calculateIntersection() {
    
    switch(count($this->_displays)){
      case 0: //no displays so everything is empty
        $this->_pageIds = array();
        $this->_elementIds = array();
        $this->_elements = array();
        break;
      case 1: //only one display so use its values
        $firstDisplay = reset($this->_displays);
        $this->_pageIds = $firstDisplay->getPageIds();
        $this->_elementIds = $firstDisplay->getElementIds();
        $this->_elements = $firstDisplay->listElements();
        break;
      default:
        $pageIds = array();
        $elementIds = array();
        $elements = array();
        foreach($this->_displays as $display){
          $pageIds[] = $display->getPageIds();
          $elementIds[] = $display->getElementIds();
          $ourElements = array();
          foreach($display->listElements() as $element){
            $ourElements[$element->type.$element->name.$element->pageId] = $element;
          }
          $elements[] = $ourElements;
        }
        
        $this->_pageIds = array_values(call_user_func_array("array_intersect", $pageIds));
        $this->_elementIds = array_values(call_user_func_array("array_intersect", $elementIds));
        $this->_elements = array_values(call_user_func_array("array_intersect_key", $elements));
    }
  }

  /**
   * Get the name of the display
   * 
   * @return string
   */
  public function getName(){
    return 'Intersection Display';
  }

  /**
   * Get id
   * 
   * @return string
   */
  public function getId(){
    return 'intersection';
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