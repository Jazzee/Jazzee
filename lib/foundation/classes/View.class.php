<?php
/**
 * Add functionality to Lvc
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 */

/**
 * A view to extend application views from
 */
class View extends Lvc_View{
	public function requireCss($cssFile){
    if($this->controller)
		  $this->controller->addCss($cssFile);
	}
  
  /**
   * Check to see if an element exists
   * @param string $name
   * @return bool
   */
  public function elementExists($name){
    return Lvc_FoundationConfig::elementExists($name);
  }
}
?>