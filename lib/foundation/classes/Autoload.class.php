<?php
/**
 * Autoloader
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 */
require_once('Foundation.class.php');

class Autoload extends Foundation {
  /**
   * Array of autoload paths
   * Used by self::autoload to store structure to search for classes
   * @var array
   */
  static private $_autoLoadPaths = array();
  
  
  /**
   * Adds a path to the autoload array
   * Will recursivly add subdirectories if the path has a / at the end
   * 
   * @param string $path the path to add
   * @param bool $prefix should we put this path in front of the others
   */
  static public function addAutoLoadPath($path, $prefix = false){
    $paths = array($path);
    //whenever a subdirectory is discovered in the array continue traversing the array
    //should allow for infinite direcotry recursion
    reset ($paths);
    while (list($key, $path) = each ($paths)) {
      $handle = opendir($path);
      while (false !== ($file = readdir($handle))) {
        if($file != "." && $file != ".." && is_dir($path . $file)){
          $paths[] = $path . $file . '/'; 
        }
      }
      closedir($handle);
    }
    if($prefix) self::$_autoLoadPaths = array_merge($paths, self::$_autoLoadPaths);
    else self::$_autoLoadPaths = array_merge(self::$_autoLoadPaths,$paths);
  }
  
  /**
   * Autoloads includes when a class is called
   * Will search any path in self::$_autoLoadPaths array for the class and include it if it is found
   * 
   * @param string $className The name of the class
   * @return bool TRUE on success FALSE on failure
   */
  static public function load($className){
    $suffixes = array('.class.php', '.inerface.php');
    foreach(self::$_autoLoadPaths as $path){
      foreach($suffixes as $suffix){
        $fileName = $path . $className . $suffix;
        if(file_exists($fileName) AND include_once($fileName)) {
          return TRUE;
        }
      }
    }
    //if we never found the class then return false to let other autoloads handle it
    return false;
  }
}

// Check to see if we already have an __autoload defined, and if so, register it.
if (function_exists('__autoload')){
  spl_autoload_register('__autoload');
}

//add the Foundation autoload function to the stack
spl_autoload_register('Autoload::load');
?>
