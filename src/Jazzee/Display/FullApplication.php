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
  
  protected $_elements = array();
  
  protected $_displayArray = array();
  
  /**
   * Construcutor takes the application we want to display
   * @param \Jazzee\Entity\Application $application
   */
  public function __construct(\Jazzee\Entity\Application $application) {
    $applicantElements = array(
      'firstName' => 'First Name',
      'lastName' => 'Last Name',
      'email' => 'Email',
      'createdAt' => 'Created At',
      'updatedAt' => 'Updated At',
      'lastLogin' => 'Last Login',
      'percentComplete' => 'Progress',
      'isLocked' => 'Locked',
      'hasPaid' => 'Paid',
      'attachments' => 'Attachments'
    );
    $weight = 0;
    foreach($applicantElements as $name => $title){
      $this->_elements[] = new \Jazzee\Display\Element('applicant', $title, $weight++, $name, null);
    }
    $pages = array();
    foreach($application->getApplicationPages() as $applicationPage){
      if(is_subclass_of($applicationPage->getPage()->getType()->getClass(), 'Jazzee\Interfaces\DataPage')){
        foreach($applicationPage->getJazzeePage()->listDisplayElements() as $displayElement){
          $this->_elements[] = $displayElement;
          if($displayElement->type == 'page'){
            $this->_pageIds[] = $displayElement->pageId;
          } else if ($displayElement->type == 'element'){
            $this->_elementIds[] = $displayElement->name;
            $this->_pageIds[] = $application->getElementById($displayElement->name)->getPage()->getId();
          }
        }
      }
    }
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
   * Get id
   * 
   * @return string
   */
  public function getId(){
    return 'full';
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