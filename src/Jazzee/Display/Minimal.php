<?php
namespace Jazzee\Display;

/**
 * Minimal Applicaiton display
 * A builtin display for showing just names and meta data
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Minimal implements \Jazzee\Interfaces\Display
{ 
  
  /**
   * Construcutor takes the application we want to display
   * @param \Jazzee\Entity\Application $application
   */
  public function __construct(\Jazzee\Entity\Application $application) {
    
  }

  /**
   * Get the name of the display
   * 
   * @return string
   */
  public function getName(){
    return 'Minimal';
  }

  /**
   * Get id
   * 
   * @return string
   */
  public function getId(){
    return 'min';
  }
  
  /**
   * Get an array of page ids that are shown by the display
   * 
   * @return array
   */
  public function getPageIds(){
    return array();
  }
  
  /**
   * Get an array of elemnet IDs that are returned by the display
   * 
   * @return array
   */
  public function getElementIds(){
    return array();
  }
  
  /**
   * Should a page be displayed
   * 
   * @param \Jazzee\Entity\Page $page
   * 
   * @return boolean
   */
  public function displayPage(\Jazzee\Entity\Page $page){
    return false;
  }
  
  /**
   * Should an Element be displayed
   * @param \Jazzee\Entity\Element $element
   * 
   * @return boolean
   */
  public function displayElement(\Jazzee\Entity\Element $element){
    return false;
  }

  public function isCreatedAtDisplayed() {
    return true;
  }

  public function isEmailDisplayed() {
    return true;
  }

  public function isFirstNameDisplayed() {
    return true;
  }

  public function isHasPaidDisplayed() {
    return true;
  }

  public function isLastLoginDisplayed() {
    return true;
  }

  public function isLastNameDisplayed() {
    return true;
  }

  public function isPercentCompleteDisplayed() {
    return true;
  }

  public function isUpdatedAtDisplayed() {
    return true;
  }
  
  public function isIsLockedDisplayed() {
    return true;
  }
}