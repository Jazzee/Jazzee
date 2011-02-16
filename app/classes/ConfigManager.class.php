<?php
/**
 * Interface with the Config Model
 */
class ConfigManager {
  /**
   * Get a value
   * @param string $name
   * @return string|null if the property isn't set
   */
  public function __get($name){
    $item = Doctrine::getTable('JazzeeConfig')->findOneByName($name);
    if($item) return $item->value;
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
      $item->value = $value;
    } else {
      $item = new JazzeeConfig;
      $item->name = $name;
      $item->value = $value;
    }
    $item->save();
  }
}
?>