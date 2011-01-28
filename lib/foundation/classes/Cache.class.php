<?php
/**
 * Singleton for caching variables
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package foundation
 */
require_once('Foundation.class.php');
class Cache extends Foundation {
  /**
   * The types of caching
   */
  const LOCAL = 0;
  const APC = 2;
  
  /**
   * The time for the cache to live in seconds
   */
  const TTL=120;
  
  /**
   * Holds the instance for singleton
   * @var Theme
   */
  private static $_instance = null;
  
  /**
   * If we don't have acccess to another caching mechanism we use a local store
   * @var array
   */
  protected $cache = array();
  
  /**
   * The caching method we are using
   * @var integer
   */
  protected $method = self::LOCAL;
  /**
   * Looks for different caching systems and picks one to use
   * Constructor is protected to force a singleton
   */
  protected function __construct(){
    if(function_exists('apc_store')){
      $this->method = self::APC;
    }
  }
  
  /**
   * Fetch a single instance of Cache 
   * @return Cache
   */
  public static function getInstance(){
    if(is_null(self::$_instance)){
      self::$_instance = new Cache;
    }
    return self::$_instance;
  }
  
  /**
   * Store an item in the cache
   * @param string $name
   * @param mixed $value
   */
  public function store($name, $value){
    switch($this->method){
      case self::APC:
        apc_store($name,$value,self::TTL);
        break;
      default:
        $this->cache[$name] = $value;
    }
  }
  
 /**
   * Fetch an item from the cache
   * @param string $name
   */
  public function fetch($name){
    switch($this->method){
      case self::APC:
        return apc_fetch($name);
        break;
      default:
        if(isset($this->cache[$name])) return $this->cache[$name];
        return false;
    }
  }
  
  /**
   * Delete an item from the cache
   * @param string $name
   */
  public function delete($name){
    switch($this->method){
      case self::APC:
        return apc_delete($name);
        break;
      default:
        unset($this->cache[$name]);
        return true;
    }
  }
}
?>