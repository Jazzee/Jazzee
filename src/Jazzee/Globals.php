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
   * The Jazzee entity manager
   * @var \Doctrine\ORM\EntityManager
   */
  protected $_entityManager;
  
  /**
   *
   * @var \Jazzee\FileStore
   */
  protected $_fileStore;
  
  /**
   * This instance
   * @var \Jazzee\Global
   */
  protected static $_instance;
  
  /**
   * Protected constructor for singleton
   * 
   */
  protected function __construct()
  {
      $this->_jazzeeConfiguration = null;
      $this->_entityManager = null;
      $this->_fileStore = null;
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
    if(is_null($this->_jazzeeConfiguration)){
      throw new \Jazzee\Exception('Attempted to get the configuration from \Jazzee\Globals before it was set.');
    }
    return $this->_jazzeeConfiguration;
  }
  
  /**
   * Get the entity manager
   * @return \Doctrine\ORM\EntityManager
   */
  public function getEntityManager()
  {
    if(is_null($this->_entityManager)){
      throw new \Jazzee\Exception('Attempted to get the entity manager from \Jazzee\Globals before it was set.');
    }
    return $this->_entityManager;
  }
  
  /**
   * Set the configuration
   * @param \Jazzee\Configuration $config
   */
  protected function setThisConfig(\Jazzee\Configuration $config)
  {
    $this->_jazzeeConfiguration = $config;
  }
  
  /**
   * Set the entity manager
   * @param \Doctrine\ORM\EntityManager $entityManager
   */
  protected function setThisEntityManager(\Doctrine\ORM\EntityManager $entityManager)
  {
    $this->_entityManager = $entityManager;
  }
  
  /**
   * Set the configuration
   * @param \Jazzee\Configuration $config
   */
  public static function setConfig(\Jazzee\Configuration $config)
  {
    \Jazzee\Globals::getInstance()->setThisConfig($config);
  }
  
  /**
   * Set the entity manager
   * @param \Doctrine\ORM\EntityManager $entityManager
   */
  public static function setEntityManager(\Doctrine\ORM\EntityManager $entityManager)
  {
    \Jazzee\Globals::getInstance()->setThisEntityManager($entityManager);
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

  public static function getFileStore()
  {
    return \Jazzee\Globals::getInstance()->getThisFileStore();
  }

  protected function getThisFileStore()
  {
    if(is_null($this->_fileStore)){
      $this->_fileStore = new \Jazzee\FileStore($this->_entityManager, $this->_jazzeeConfiguration->getVarPath() . '/cache/');
    }

    return $this->_fileStore;
  }
}