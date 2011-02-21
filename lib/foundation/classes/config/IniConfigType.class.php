<?php
/**
 * The ini file type
 */
class IniConfigType implements ConfigType{
  /**
   * @var string $path to the file
   */
  protected $path;
  
  /**
   * @var array $variables
   */
  protected $variables;
  
  /**
   * Read the file and store the variables
   * @param string $path to the file
   */
  public function __construct($path){
    if(!is_readable($path)){
      throw new Foundation_Exception("Unable to read INI file at {$path}");
    }
    $this->path = $path;
    $this->variables = parse_ini_file($this->path);
    if($this->variables === false) throw new Foundation_Exception("Unable to parse INI file at {$path}");
  }
  
  public function listVariables(){
    return array_keys($this->variables);
  }
  
  /**
   * Read a variable value
   * @param string $name
   * @return string
   */
  public function readVar($name){
    if(isset($this->variables[$name])) return $this->variables[$name];
    return null;
  }
  
  /**
   * Write a variable value
   * @param string $name
   * @param mixed $value
   */
  public function writeVar($name, $value){
    throw new Foundation_Exception("Tried to write {$name} to INIFileConfigType.  No writing is availabe for INI files");
  }
}