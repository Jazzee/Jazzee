<?php
namespace Jazzee\Entity\Answer;
/**
 * Recommenders
 */
class Recommenders extends Standard
{
  
  public function applyStatus(){
    $arr = parent::applyStatus();
    if($child = $this->_answer->getChildren()->first()){
      $arr['Status'] = 'This recommendation was recieved on ' . $child->getUpdatedAt()->format('l F jS Y g:ia');
    } else if($this->_answer->isLocked()){
      $arr['Invitation Sent'] = $this->_answer->getUpdatedAt()->format('l F jS Y g:ia');
      $arr['Status'] = 'You cannot make changes to this recommendation becuase the invitation has already been sent.  You will be able to resend the invitation in ' . (floor((time() - $this->_answer->getUpdatedAt()->format('U') + \Jazzee\Entity\Page\Recommenders::RECOMMENDATION_EMAIL_WAIT_TIME)/86400)) . ' days';
    }
    return $arr;
  }
  
  public function applyTools(){
    $arr = array();
    if(!$this->_answer->isLocked()){
      $arr = parent::applyTools();
      $arr['Send Invitation'] = '/do/sendEmail/' . $this->_answer->getId();
      //if there is no recommendation response and it has been more than the required elapsed time allow the email to be resent.
    } else if(!$this->_answer->getChildren()->count() AND time() - $this->_answer->getUpdatedAt()->format('U') > \Jazzee\Entity\Page\Recommenders::RECOMMENDATION_EMAIL_WAIT_TIME){
      $arr['Resend Invitation'] = '/do/sendEmail/' . $this->_answer->getId();
    }
    return $arr;
  }
}