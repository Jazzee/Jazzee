<?php
/**
 * Communication
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 */
class Communication extends BaseCommunication{
  /**
   * Pre Insert set the createdAt timestamp
   * @see Doctrine_Record::preInsert()
   */
  public function preInsert(Doctrine_Event $event){
    $modifiedFields = $this->getModified();
    if(!array_key_exists('createdAt',$modifiedFields)){
      $this->createdAt = date('Y-m-d H:i:s', time());
    }
  }
}