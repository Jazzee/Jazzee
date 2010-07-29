<?php
/**
 * Foundation base controller
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package foundation
 */
class Controller extends Lvc_PageController{
  
  /**
   * Specifies the layout to be used in displaying the view
   * @var string
   */
	protected $layout = THM_NAME;
  
  /**
   * Any actions parameters which were passed in by the Lvc_Router from the URL
   * @var array
   */
  protected $actionParams = array();
  
  /**
   * Stores the actionParams and then executes the parent runAction
   * @param string $actionName the action name to run.
   * @param array $actionParams the parameters to pass to the action.
   * @return void
   **/
  public function runAction($actionName, &$actionParams = array()) {
    $this->actionParams = $actionParams;
    parent::runAction($actionName, $actionParams);
  }
  
  /**
   * Add a css file to be loaded
   * @param string $cssFile
   * @return null
   */
	public function addCss($cssFile){
		$this->layoutVars['requiredCss'][$cssFile] = true;
	}
	
  /**
   * Add a javascript file to be loaded
   * @param string $jsFile
   * @return null
   */
	public function addScript($jsFile){
		$this->layoutVars['requiredJs'][$jsFile] = true;
	}
  
  /**
   * Get ControllerAuthObject
   * @return ControllerAuth or null;
   */
  public static function getControllerAuth(){
    return null;
  }
}

/**
 * Define Authorization settings for a controller
 */
class ControllerAuth {
  /**
   * A human readable name for the controllers functionality
   * @var string
   */
  public $name;
  
  /**
   * An array of ActionAuth classes for the controllers actions
   * @var array
   */
  protected $actions;
  
  /**
   * Add an action
   * @param string $name the name of the action
   * @param ActionAuth $action
   */
  public function addAction($name, ActionAuth $action){
    $this->actions[$name] = $action;
  }
  
  /**
   * Get the actions
   */
  public function getActions(){
    return $this->actions;
  }
}

/**
 * Authorization for a single action
 */
class ActionAuth {
  /**
   * A human readable name for the action's functionality
   * @var string
   */
  public $name;
  
  /**
   * Constructor
   * @param string $name
   */
  public function __construct($name = ''){
    $this->name = $name;
  }
}
?>