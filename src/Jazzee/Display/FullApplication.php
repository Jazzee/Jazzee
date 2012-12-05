<?php
namespace Jazzee\Display;

/**
 * Full Applicaiton display
 * A builtin display for showing the entire application 
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class FullApplication implements \Jazzee\Interfaces\Display
{ 
  protected $_pageIds = array();
  
  protected $_elementIds = array();
  
  protected $_displayArray = array();
  
  /**
   * Construcutor takes the application we want to display
   * @param \Jazzee\Entity\Application $application
   */
  public function __construct(\Jazzee\Entity\Application $application) {
    $pages = array();
    foreach($application->getApplicationPages() as $applicationPage){
      if(is_subclass_of($applicationPage->getPage()->getType()->getClass(), 'Jazzee\Interfaces\DataPage')){
        $pageArr = array(
          'id' => $applicationPage->getPage()->getId(),
          'title' => $applicationPage->getTitle(),
          'elements' => array()
        );
        $this->_pageIds[] = $applicationPage->getPage()->getId();
        foreach($applicationPage->getPage()->getElements() as $element){
          $pageArr['elements'][] = array(
            'id' => $element->getId(),
            'title' => $element->getTitle(),
          );
          $this->_elementIds[] = $element->getId();
        }
        $pages[] = $pageArr;
      }
    }
    $this->_displayArray['pages'] = $pages;
  }

  /**
   * Get the name of the display
   * 
   * @return string
   */
  public function getName(){
    return 'Full Application';
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
  
  public function toArray(){
    return $this->_displayArray;
  }
}