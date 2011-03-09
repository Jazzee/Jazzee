<?php
/**
 * Configuration Manager
 * Normalizes working with different configuration types without needing to know anything about the type
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package foundation
 *
 */
class ConfigManager {
  protected $objects = array();
  protected $variables = array();
  /**
   * Get a value
   * @param string $name
   * @return string|null if the property isn't set
   */
  public function __get($name){
    if(isset($this->variables[$name])){
      return $this->objects[$this->variables[$name]]->readVar($name);
    }
    return null;
  }
  
  /**
   * Set a value
   * @param string $name
   * @param string $value
   */
  public function __set($name, $value){
    if(isset($this->variables[$name])){
      return $this->objects[$this->variables[$name]]->writeVar($name, $value);
    }
    throw new Foundation_Exception("Tried to save {$name}, but there is no ConfigType with that variable");
  }
  
  /**
   * Check if a value is set
   * @param string $name
   * @return boolean
   */
  public function __isset($name){
    return isset($this->variables[$name]);
  }
  
  /**
   * Add a config container
   * @param ConfigType $container
   */
  public function addContainer(ConfigType $type){
    $this->objects[] = $type;
    $id = count($this->objects) - 1;
    $variables = $type->listVariables();
    foreach($variables as $key){
      if(array_key_exists($key, $this->variables)){
        throw new Foundation_Exception("{$key} varialbe is already present in another container");
      }
      $this->variables[$key] = $id;
    }
  }
}