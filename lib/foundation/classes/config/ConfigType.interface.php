<?php
/**
 * Interface for Configuration types
 */
interface ConfigType{
  /**
   * List all the variables in a ConfigType
   */
  public function listVariables();
  public function readVar($name);
  public function writeVar($name, $value);
}