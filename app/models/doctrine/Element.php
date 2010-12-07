<?php

/**
 * Element
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 */
class Element extends BaseElement{
  /**
  * Get ListItem
  * @param integer $itemId
  * @return ListItem
  */
  public function getItemById($itemId){
    $key = array_search($itemId, $this->ListItems->getPrimaryKeys());
    if($key !== false){ //use === becuase 0 is returned often
      return $this->ListItems->get($key);
    }
    return false;
  }
}