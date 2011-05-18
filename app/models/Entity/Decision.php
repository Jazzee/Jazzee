<?php
namespace Entity;

/** 
 * Applicant
 * Individual applicants are tied to an Application - but a single person can be multiple Applicants
 * @Entity @Table(name="decisions") 
 * @package    jazzee
 * @subpackage orm
 **/
class Decision{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** 
   * @OneToOne(targetEntity="Applicant",inversedBy="decision",cascade={"all"})
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE") 
   */
  private $applicant;
  
  /** @Column(type="datetime") */
  private $nominateAdmit;
  
  /** @Column(type="datetime") */
  private $nominateDeny;
  
  /** @Column(type="datetime") */
  private $finalAdmit;
  
  /** @Column(type="datetime") */
  private $finalDeny;
  
  /** @Column(type="datetime") */
  private $offerResponseDeadline;
  
  /** @Column(type="datetime") */
  private $decisionLetterSent;
  
  /** @Column(type="datetime") */
  private $decisionLetterViewed;
  
  /** @Column(type="datetime") */
  private $acceptOffer;
  
  /** @Column(type="datetime") */
  private $declineOffer;

  
  /**
   * Format a decision time stamp
   */
  protected function decisionStamp(){
    return DateTime('now');
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