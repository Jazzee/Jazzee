<?php
/**
 * A single navigtion menu
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage navigation
 */
class Navigation_Menu {
  /**
   * The title for this menu
   * @var string
   */
  public $title;
  
  /**
   * holds the links
   * @var array
   */
  private $_links = array();
  
  
  /**
   * Create a new link object
   * @param array $attributes
   * @return Navigation_Link
   */
  public function newLink($attributes = array()){
    $link = new Navigation_Link;
    foreach($attributes as $key=>$value){
      $link->$key = $value;
    }
    $this->_links[] = $link;
    return $link;
  }
  
  /**
   * Get the links
   * return array
   */
  public function getLinks(){
    return $this->_links;
  }
  
  /**
   * Does the menu have links
   * @return bool true if there are any links false if not
   */
  public function hasLink(){
    return (bool)count($this->_links);
  }
}
?>