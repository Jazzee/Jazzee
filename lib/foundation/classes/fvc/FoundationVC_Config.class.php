<?php
/**
 * FoundationVC
 * 
 * Mostly copied from LightVC - A lightweight view-controller framework.
 * http://lightvc.org/
 * 
 * The original framework Copyright (c) 2007, Anthony Bush has been modified to meet the caching and 
 * detection needs of our Foundation framework.
 * 
 * @author Anthony Bush
 * @author Jonathan Johnson
 * @version 1.0.4-foundation (2011-02-01)
 * @package lightvc
 * @see http://lightvc.org/
 **/

/**
 * Configuration class for the LVC suite of classes.
 *
 * @package lightvc
 * @author Anthony Bush
 * @since 2007-04-20
 **/
class FoundationVC_Config extends Lvc_Config {
  /**
   *  Our cache
   *  @var Cache 
   */
  static private $_cache;
  
  /**
   * What we prefix our cached paths with
   * @const string
   */
  const CACHE_PREFIX = 'FoundationVCPath';
  
  public static function prefixControllerPath($path) {
    array_unshift(self::$controllerPaths, $path);
  }
  public static function prefixControllerViewPath($path) {
    array_unshift(self::$controllerViewPaths, $path);
  }
  public static function prefixLayoutViewPath($path) {
    array_unshift(self::$layoutViewPaths, $path);
  }
  public static function prefixElementViewPath($path) {
    array_unshift(self::$elementViewPaths, $path);
  }
	
	// Retrieval Methods
 
  /**
   * Get the path to a controller include
   * @param string $controllerName
   * @return string path to controller
   */
  public static function getControllerPath($controllerName) {
    if(!isset(self::$_cache)) self::$_cache = Cache::getInstance();
    if($cachePath = self::$_cache->fetch(self::CACHE_PREFIX . 'Controller' . $controllerName)){
      return $cachePath;
    }
    foreach (self::$controllerPaths as $path) {
      $file = $path . $controllerName . self::$controllerSuffix;
      if (file_exists($file)) {
        self::$_cache->store(self::CACHE_PREFIX . 'Controller' . $controllerName,$file);
        return $file;
      }
    }
    throw new Foundation_Exception("Path to {$controllerName} can not be found");
  }
  
  /**
   * Get the path to a view
   * @param string $viewName
   * @param string $viewType (Controller,Element,Layout)
   * @param array $paths
   * @param string $suffix
   * @return string path to view file
   */
  public static function getViewPath($viewName, $viewType, $paths, $suffix) {
    if(!isset(self::$_cache)) self::$_cache = Cache::getInstance();
    if($cachePath = self::$_cache->fetch(self::CACHE_PREFIX . $viewType .'View' . $viewName)){
      return $cachePath;
    }
    foreach ($paths as $path) {
      $file = $path . $viewName . $suffix;
      if (file_exists($file)) {
        self::$_cache->store(self::CACHE_PREFIX . $viewType .'View' . $viewName,$file);
        return $file;
      }
    }
    throw new Foundation_Exception("Path to {$viewType} {$viewName} can not be found");
  }
  
  /**
   * Include a controller
   * @param string $controlerName
   */
  public static function includeController($controllerName){
    $file = self::getControllerPath($controllerName);
    include_once($file);
  }
  
	public static function getController($controllerName) {
	  $file = self::getControllerPath($controllerName);
		include_once($file);
		$controllerClass = self::getControllerClassName($controllerName);
		$controller = new $controllerClass();
		$controller->setControllerName($controllerName);
		return $controller;
	}
	
  public static function getControllerView($viewName, &$data = array()) {
    $file = self::getViewPath($viewName, 'controller', self::$controllerViewPaths, self::$controllerViewSuffix);
    return new self::$viewClassName($file, $data);
	}
	
	public static function getElementView($elementName, &$data = array()) {
	  $file = self::getViewPath($elementName, 'element', self::$elementViewPaths, self::$elementViewSuffix);
    return new self::$viewClassName($file, $data);
	}
	
	public static function getLayoutView($layoutName, &$data = array()) {
	  $file = self::getViewPath($layoutName, 'layout', self::$layoutViewPaths, self::$layoutViewSuffix);
    return new self::$viewClassName($file, $data);
	}
  
  /**
   * See if an element exists
   * @param string $elementName
   */
  public static function elementExists($elementName){
    if(!isset(self::$_cache)) self::$_cache = Cache::getInstance();
    if($cachePath = self::$_cache->fetch(self::CACHE_PREFIX . 'Element' . $elementName)){
      return true;
    }
    foreach(self::$elementViewPaths as $path){
      $file = $path . $elementName . self::$elementViewSuffix;
      if (file_exists($file)) {
        self::$_cache->store(self::CACHE_PREFIX . 'Element' . $elementName,$file);
        return true;
      }
    }  
    return false;
  }
}