<?php

namespace Jazzee;

/**
 * A container for global functions as static methods
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Globals
{
  /**
   * The Jazzee configuration
   * @var \Jazzee\Configuration
   */
  protected $_jazzeeConfiguration;
  
  /**
   * This instance
   * @var \Jazzee\Global
   */
  protected static $_instance;
  
  /**
   * Protected constructor for singleton
   * 
   */
  protected function __construct(){
    $this->_jazzeeConfiguration = new \Jazzee\Configuration();
  }
  
  /**
   * Instances of Globals are only available to global static methods
   * 
   * @return \Jazzee\Globals
   */
  protected static function getInstance(){
    if(!self::$_instance){
      self::$_instance = new Globals();
    }
    
    return self::$_instance;
  }
  
  /**
   * Get the configuration
   * @return \Jazzee\Configuration
   */
  public function getConfig(){
    return $this->_jazzeeConfiguration;
  }

  /**
   * Store a file
   *
   * @param string $filename
   * @param blob $blob
   */
  public static function storeFile($filename, $blob)
  {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $safeName = md5($filename);
    file_put_contents(\Jazzee\Globals::getInstance()->getConfig()->getVarPath() . '/tmp/' . $safeName . '.' . $ext, $blob);
    $session = new \Foundation\Session();
    $store = $session->getStore('files');
    $store->set($safeName, $filename);
  }

  /**
   * Get a stored file
   *
   * @param string $filename
   * @return \Foundation\Virtual\RealFile
   */
  public static function getStoredFile($filename)
  {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $safeName = md5($filename);
    $path = \Jazzee\Globals::getInstance()->getConfig()->getVarPath() . '/tmp/' . $safeName . '.' . $ext;
    $session = new \Foundation\Session();
    $store = $session->getStore('files');
    if (is_readable($path) and $store->check($safeName) and $store->get($safeName) == $filename) {
      return new \Foundation\Virtual\RealFile($filename, $path);
    }

    return false;
  }

  /**
   * Remove a stored file
   *
   * @param string $filename
   */
  public static function removeStoredFile($filename)
  {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $safeName = md5($filename);
    $path = \Jazzee\Globals::getInstance()->getConfig()->getVarPath() . '/tmp/' . $safeName . '.' . $ext;
    if (is_writable($path)) {
      unlink($path);
    }
  }

  /**
   * Path to a resource
   * @param string $path
   * 
   * @return string
   */
  public static function path($path)
  {
    return rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\.') . '/' . $path;
  }
}