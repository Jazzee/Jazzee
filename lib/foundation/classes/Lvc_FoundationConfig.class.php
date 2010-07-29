<?php
/**
 * Override Lvc_Config to add some functionality
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 **/
class Lvc_FoundationConfig extends Lvc_Config {
  
  /**
   * Get the path to a controller include
   * @param string $controllerName
   * @return string path to controller
   */
  public static function getControllerPath($controllerName) {
    foreach (self::$controllerPaths as $path) {
      $file = $path . $controllerName . self::$controllerSuffix;
      if (file_exists($file)) {
        return $file;
      }
    }
    throw new Foundation_Exception("Path to {$controllerName} can not be found");
  }
  
  /**
   * Include a controller
   * @param string $controlerName
   */
  public static function includeController($controllerName){
    $file = self::getControllerPath($controllerName);
    include_once($file);
  }
  
  public static function elementExists($elementName){
    $exists = false;
    foreach(self::$elementViewPaths as $path){
      $file = $path . $elementName . self::$elementViewSuffix;
      if (file_exists($file)) {
        $exists = true;
        break;
      }
    }  
    return $exists;
  }
  
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
}
?>