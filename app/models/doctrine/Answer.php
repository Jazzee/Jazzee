<?php

/**
 * Answer
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 */
class Answer extends BaseAnswer{
  /**
   * Whenever we are saved update the timestamp
   * @param $event Doctrine_Event
   */
  public function preSave(Doctrine_Event $event){
    if(!$this->isModified() AND !$this->Elements->isModified()) return;
    $modifiedFields = $this->getModified();
    if ( ! array_key_exists('updatedAt',$modifiedFields)) {
      $this->updatedAt = date('Y-m-d H:i:s', time());
    }
  }
  
  
  /**
   * Find child answer by pageID
   * @param integer $id
   * @return Answer || NULL
   */
  public function getChildByPageId($id){
    foreach($this->Children as $child){
      if($child->pageID == $id) return $child;
    }
    return false;
  }
  
}