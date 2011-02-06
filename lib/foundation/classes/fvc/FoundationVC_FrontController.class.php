<?php
/**
 * The Front Controller
 * Extend the original to use our FoundationVC_Config class
 * @author Jon Johnson
 *
 */
class FoundationVC_FrontController extends Lvc_FrontController {
	/**
	 * Use our FoundationVC_Config class
	 * @see Lvc_FrontController::processRequest()
	 */
	public function processRequest(Lvc_Request $request) {
		try
		{
			// Give routers a chance to (re)-route the request.
			foreach ($this->routers as $router) {
				if ($router->route($request)) {
					break;
				}
			}

			// If controller name or action name are not set, set them to default.
			$controllerName = $request->getControllerName();
			if (empty($controllerName)) {
				$controllerName = FoundationVC_Config::getDefaultControllerName();
				$actionName     = FoundationVC_Config::getDefaultControllerActionName();
				$actionParams = $request->getActionParams() + FoundationVC_Config::getDefaultControllerActionParams();
				$request->setActionParams($actionParams);
			} else {
				$actionName = $request->getActionName();
				if (empty($actionName)) {
					$actionName   = FoundationVC_Config::getDefaultActionName();
				}
			}

			$controller = FoundationVC_Config::getController($controllerName);
			if (is_null($controller)) {
				throw new Lvc_Exception('Unable to load controller "' . $controllerName . '"');
			}
			$controller->setControllerParams($request->getControllerParams());
			$controller->runAction($actionName, $request->getActionParams());
		}
		catch (Lvc_Exception $e)
		{
			// Catch exceptions and append additional error info if the request object has anything to say.
			$moreInfo = $request->getAdditionalErrorInfo();
			if (!empty($moreInfo)) {
				throw new Lvc_Exception($e->getMessage() . '. ' . $moreInfo);
			} else {
				throw $e;
			}
		}
	}
}