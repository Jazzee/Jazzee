<?php
/**
 * Interface with the Config Model
 */
class ConfigManager {
  /**
   * All the current config variables
   * @var array
   */
  protected $properties = array();
  
  /**
   * Constructor
   */
  public function __construct(){
    $this->fillProperties();
  }
  
  /**
   * Fill the $vars array with table values
   */
  protected function fillProperties(){
    $items = Doctrine::getTable('JazzeeConfig')->findAll(Doctrine::HYDRATE_ARRAY);
    foreach($items as $item){
      $this->properties[$item['name']] = $item['value'];
    }
  }
  
  /**
   * Get a value
   * @param string $name
   * @return string|null if the property isn't set
   */
  public function __get($name){
    if(array_key_exists($name, $this->properties)){
      return $this->properties[$name];
    }
    $trace = debug_backtrace();
    trigger_error(
      'Undefined property : ' . $name .
      ' in ' . $trace[0]['file'] .
      ' on line ' . $trace[0]['line'],
      E_USER_NOTICE);
    return null;
  }
  
  /**
   * Set a value
   * @param string $name
   * @param string $value
   */
  public function __set($name, $value){
    $item = Doctrine::getTable('JazzeeConfig')->findOneByName($name);
    if($item){
      $item->value = (string)$value;
    } else {
      $item = new JazzeeConfig;
      $item->name = $name;
      $item->value = (string)$value;
    }
    $item->save();
    $this->fillProperties();
  }
  
    
}
?>