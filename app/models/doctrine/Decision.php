<?php
/**
 * Decision
 * 
 * @property integer $applicantID
 * @property timestamp $nominateAdmit
 * @property timestamp $nominateDeny
 * @property timestamp $finalAdmit
 * @property timestamp $finalDeny
 * @property timestamp $offerResponseDeadline
 * @property timestamp $decisionLetterSent
 * @property timestamp $decisionLetterViewed
 * @property timestamp $acceptOffer
 * @property timestamp $declineOffer
 * @property Applicant $Applicant
 * 
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 */
class Decision extends Doctrine_Record{
  
  /**
   * @see Doctrine_Record::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('decision');
    $this->hasColumn('applicantID', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('nominateAdmit', 'timestamp', null, array(
      'type' => 'timestamp',
     ));
    $this->hasColumn('nominateDeny', 'timestamp', null, array(
      'type' => 'timestamp',
     ));
    $this->hasColumn('finalAdmit', 'timestamp', null, array(
      'type' => 'timestamp',
     ));
    $this->hasColumn('finalDeny', 'timestamp', null, array(
      'type' => 'timestamp',
     ));
    $this->hasColumn('offerResponseDeadline', 'timestamp', null, array(
      'type' => 'timestamp',
     ));
    $this->hasColumn('decisionLetterSent', 'timestamp', null, array(
      'type' => 'timestamp',
     ));
    $this->hasColumn('decisionLetterViewed', 'timestamp', null, array(
      'type' => 'timestamp',
     ));
    $this->hasColumn('acceptOffer', 'timestamp', null, array(
      'type' => 'timestamp',
     ));
    $this->hasColumn('declineOffer', 'timestamp', null, array(
      'type' => 'timestamp',
     ));
  }

  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasOne('Applicant', array(
      'local' => 'applicantID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );
  }
  
  /**
   * Format a decision time stamp
   */
  protected function decisionStamp(){
    return date('Y-m-d H:i:s');
  }
  
  /**
   * Nominate for admit decision
   */
  public function nominateAdmit(){
    if(!is_null($this->nominateDeny)){
      throw new Jazzee_Exception('Cannot record two preliminary decisions');
    }
    if(is_null($this->nominateAdmit)) $this->nominateAdmit = $this->decisionStamp();
  }
  
  /**
   * Undo nominate admit
   */
  public function undoNominateAdmit(){
    if(!is_null($this->finalAdmit)){
      throw new Jazzee_Exception('Cannot remove a preliminary decision if a final decision has been recorded');
    }
    $this->nominateAdmit = null;
  }
  
  /**
   * Nominate for deny decision
   */
  public function nominateDeny(){
    if(!is_null($this->nominateAdmit)){
      throw new Jazzee_Exception('Cannot record two preliminary decisions');
    }
    if(is_null($this->nominateDeny)) $this->nominateDeny = $this->decisionStamp();
  }
  
  /**
   * Undo nominate deny
   */
  public function undoNominateDeny(){
    if(!is_null($this->finalDeny)){
      throw new Jazzee_Exception('Cannot remove a preliminary decision if a final decision has been recorded');
    }
    $this->nominateDeny = null;
  }
  
  /**
   * Final Deny Decision
   */
  public function finalDeny(){
    if(!is_null($this->finalAdmit)){
      throw new Jazzee_Exception('Cannot record two final decisions');
    }
    //if we don't have a preliminary decision record it now
    if(is_null($this->nominateDeny)) $this->nominateDeny = $this->decisionStamp();
    if(is_null($this->finalDeny)) $this->finalDeny = $this->decisionStamp();
  }
  
  /**
   * Undo Final Deny Decision
   */
  public function undoFinalDeny(){
    $this->finalDeny = null;
  }
  

  /**
   * Final Admit Decision
   */
  public function finalAdmit(){
    if(!is_null($this->finalDeny)){
      throw new Jazzee_Exception('Cannot record two final decisions');
    }
    //if we don't have a preliminary decision record it now
    if(is_null($this->nominateAdmit)) $this->nominateAdmit = $this->decisionStamp();
    if(is_null($this->finalAdmit)) $this->finalAdmit = $this->decisionStamp();
  }
  
  /**
   * Undo Final Deny Decision
   */
  public function undoFinalAdmit(){
    $this->finalAdmit = null;
  }
  
  /**
   * Accept Offer
   */
  public function acceptOffer(){
    if(is_null($this->finalAdmit)){
      throw new Jazzee_Exception('Cannot accept offer for an applicant who has not been admitted');
    }
    if(is_null($this->acceptOffer)) $this->acceptOffer = $this->decisionStamp();
  }
  
  /**
   * Decline Offer
   */
  public function declineOffer(){
    if(is_null($this->finalAdmit)){
      throw new Jazzee_Exception('Cannot decline offer for an applicant who has not been admitted');
    }
    if(is_null($this->declineOffer)) $this->declineOffer = $this->decisionStamp();
  }
  
  /**
   * Register a decision letter has been sent
   */
  public function decisionLetterSent(){
    $this->decisionLetterSent = $this->decisionStamp();
  }
}