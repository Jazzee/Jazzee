<?php
/**
 * A Ranking Element
 * Displays multiple identical select boxes for ranking choices
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_RankingListElement extends Form_ListElement{
  /**
   * The Number of items to rank
   * @var integer
   */
  public $rankItems;
  
  /**
   * The minimum required items which must be ranked
   * @var integer
   */
  public $minimumItems;
  /**
   * Constructor
   * Make $value and array
   */
  public function __construct(Form_Field $field){
    parent::__construct($field);
    $this->value = array();
    $this->rankItems = 0;
    $this->minimumItems = 0;
  }
  
  /**
   * Set the value
   * Checkboxes use an array of values since multiple items can be checked
   * @param $value string|array
   */
  public function setValue($value){
    if(is_array($value)){
      foreach($value as $v){
        $this->value[] = $v;
      }
    } else {
      $this->value[] = $value;
    }
  }
  
  /**
   * Check to be sure items and minimumItems are set then run the parent method
   */
  public function preRender(){
    if(!$this->rankItems OR !$this->minimumItems){
      throw new Foundation_Exception('RankingListElement requires items and minimumItems to be set before it is rendered.');
    }
    parent::preRender();
  }
    
  /**
   * Validate that the minimum answers have been submitted and that there are no duplicated
   * Then run the parent
   * @param FormInput $input
   */
  public function validate(FormInput $input){
    $values = array();
    for($i = 0; $i < $this->rankItems; $i++){
      if($value = $input->{$this->name}[$i]){
        if(in_array($value, $values)){
          $this->addMessage('You have selected the same item twice');
          return false;
        } else {
          $values[] = $value;
        }
      }
    }
    if(count($values) < $this->minimumItems){
      $this->addMessage('You must rank at least ' . (int)$this->minimumItems . ' items');
      return false;
    }
    return parent::validate($input);
  }
  
}
?>