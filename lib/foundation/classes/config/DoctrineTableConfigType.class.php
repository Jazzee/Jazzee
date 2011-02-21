<?php
/**
 * The Doctrine Table config type
 */
class DoctrineTableConfigType implements ConfigType{
  /**
   * @var Doctrine_Table
   */
  protected $table;
  
  /**
   * @var array the variables held in the table
   */
  protected $variables = array();
  
  /**
   * Read the file and store the variables
   * @param Doctrine_Table $table
   */
  public function __construct(Doctrine_Table $table){
    $this->table = $table;
    $arr = $table->findAll(Doctrine_Core::HYDRATE_ARRAY);
    foreach($arr as $var){
      $this->variables[$var['name']] = $var['id'];
    }
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
    if(isset($this->variables[$name])) return $this->table->find($this->variables[$name])->value;
    return null;
  }
  
  /**
   * Write a variable value
   * @param string $name
   * @param mixed $value
   */
  public function writeVar($name, $value){
    if(isset($this->variables[$name])) $var = $this->table->find($this->variables[$name]);
    else $var = $this->table->get(null);
    
    $var->name = $name;
    $var->value = $value;
    $var->save();
  }
}