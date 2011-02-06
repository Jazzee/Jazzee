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
    return FoundationVC_Config::elementExists($name);
  }
  
  /**
	 * Overridden to user FoundationVC_Config
	 * @see Lvc_View::renderElement()
	 */
	protected function renderElement($elementName, $data = array()) {
		$view = FoundationVC_Config::getElementView($elementName, $data);
		if (!is_null($view)) {
			$view->setController($this->controller);
			$view->output();
		} else {
			error_log('Unable to render element "' . $elementName . '"');
			// throw new Lvc_Exception('Unable to render element "' . $elementName . '"');
		}
	}
}
?>