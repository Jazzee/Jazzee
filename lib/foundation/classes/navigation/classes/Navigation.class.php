<?php
/**
 * Site Navigation
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage navigation
 */
class Navigation{
  /**
   * @var array $_menus holds the menus
   */
  private $_menus = array();
  
  /**
   * The class to apply to the navigation container
   */
  public $class = 'navigation';
  
  /**
   * Create a new menu
   */
  public function newMenu(){
    $menu = new Navigation_Menu;
    $this->_menus[] = $menu;
    return $menu;
  }
  
  /**
   * Get the menus
   */
  public function getMenus(){
    return $this->_menus;
  }
}
?>