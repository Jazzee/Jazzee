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
  
  /**
   * After we save the Element make sure all of its Items
   * At some point doctrine is unable to follow the relationships deep enough
   * This method explicitly saves the members of collections with the correct id
   */
  public function postSave(){
    foreach($this->ListItems as $item){
      if($item->isModified(true)){
        $item->elementID = $this->id;
        $item->save();
      }
    }
  }
}