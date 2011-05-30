<?php
namespace Jazzee\Entity;

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
   * @OneToOne(targetEntity="Applicant",inversedBy="decision")
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE") 
   */
  private $applicant;
  
  /** @Column(type="datetime", nullable=true) */
  private $nominateAdmit;
  
  /** @Column(type="datetime", nullable=true) */
  private $nominateDeny;
  
  /** @Column(type="datetime", nullable=true) */
  private $finalAdmit;
  
  /** @Column(type="datetime", nullable=true) */
  private $finalDeny;
  
  /** @Column(type="datetime", nullable=true) */
  private $offerResponseDeadline;
  
  /** @Column(type="datetime", nullable=true) */
  private $decisionLetterSent;
  
  /** @Column(type="datetime", nullable=true) */
  private $decisionLetterViewed;
  
  /** @Column(type="datetime", nullable=true) */
  private $acceptOffer;
  
  /** @Column(type="datetime", nullable=true) */
  private $declineOffer;

  /**
   * Set applicant
   *
   * @param Entity\Applicant $applicant
   */
  public function setApplicant(Applicant $applicant){
    $this->applicant = $applicant;
  }
  
  /**
   * Format a decision time stamp
   * @param string $dateString
   */
  protected function decisionStamp($dateString){
    $dateString = empty($dateString)?'now':$dateString;
    return new \DateTime($dateString);
  }

  /**
   * Nominate for admit decision
   * @param string $dateString
   */
  public function nominateAdmit($dateString = null){
    if(!is_null($this->nominateDeny)){
      throw new Jazzee_Exception('Cannot record two preliminary decisions');
    }
    if(is_null($this->nominateAdmit)) $this->nominateAdmit = $this->decisionStamp($dateString);
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
   * @param string $dateString
   */
  public function nominateDeny($dateString = null){
    if(!is_null($this->nominateAdmit)){
      throw new Jazzee_Exception('Cannot record two preliminary decisions');
    }
    if(is_null($this->nominateDeny)) $this->nominateDeny = $this->decisionStamp($dateString);
  }
  
  /**
   * Undo nominate deny
   * @param string $dateString
   */
  public function undoNominateDeny(){
    if(!is_null($this->finalDeny)){
      throw new Jazzee_Exception('Cannot remove a preliminary decision if a final decision has been recorded');
    }
    $this->nominateDeny = null;
  }
  
  /**
   * Final Deny Decision
   * @param string $dateString
   */
  public function finalDeny($dateString = null){
    if(!is_null($this->finalAdmit)){
      throw new Jazzee_Exception('Cannot record two final decisions');
    }
    //if we don't have a preliminary decision record it now
    if(is_null($this->nominateDeny)) $this->nominateDeny = $this->decisionStamp($dateString);
    if(is_null($this->finalDeny)) $this->finalDeny = $this->decisionStamp($dateString);
  }
  
  /**
   * Undo Final Deny Decision
   */
  public function undoFinalDeny(){
    $this->finalDeny = null;
  }
  
  /**
   * Set offerResponseDeadline
   * @param string $dateString
   */
  public function setOfferResponseDeadline($dateString){
    $this->offerResponseDeadline = new \DateTime($dateString);
  }
  
 /**
   * Get offerResponseDeadline
   * @return DateTime|null
   */
  public function getOfferResponseDeadline(){
    return $this->offerResponseDeadline;
  }

  /**
   * Final Admit Decision
   * @param string $dateString
   */
  public function finalAdmit($dateString = null){
    if(!is_null($this->finalDeny)){
      throw new Jazzee_Exception('Cannot record two final decisions');
    }
    //if we don't have a preliminary decision record it now
    if(is_null($this->nominateAdmit)) $this->nominateAdmit = $this->decisionStamp($dateString);
    if(is_null($this->finalAdmit)) $this->finalAdmit = $this->decisionStamp($dateString);
  }
  
  /**
   * Undo Final Deny Decision
   */
  public function undoFinalAdmit(){
    $this->finalAdmit = null;
  }
  
  /**
   * Accept Offer
   * @param string $dateString
   */
  public function acceptOffer($dateString = null){
    if(is_null($this->finalAdmit)){
      throw new Jazzee_Exception('Cannot accept offer for an applicant who has not been admitted');
    }
    if(is_null($this->acceptOffer)) $this->acceptOffer = $this->decisionStamp($dateString);
  }
  
  /**
   * Decline Offer
   * @param string $dateString
   */
  public function declineOffer($dateString = null){
    if(is_null($this->finalAdmit)){
      throw new Jazzee_Exception('Cannot decline offer for an applicant who has not been admitted');
    }
    if(is_null($this->declineOffer)) $this->declineOffer = $this->decisionStamp($dateString);
  }
  
  /**
   * Register a decision letter has been sent
   * @param string $dateString
   */
  public function decisionLetterSent($dateString = null){
    $this->decisionLetterSent = $this->decisionStamp($dateString);
  }
  
  /**
   * Register a decision letter has been viewed
   * @param string $dateString
   */
  public function decisionLetterViewed($dateString = null){
    $this->decisionLetterViewed = $this->decisionStamp($dateString);
  }
   
  /**
   * get finalDeny
   *
   * @return \DateTime
   */
  public function getFinalDeny(){
    return $this->finalDeny;
  }
   
  /**
   * get finalAdmit
   *
   * @return \DateTime
   */
  public function getFinalAdmit(){
    return $this->finalAdmit;
  }
   
  /**
   * get nominateDeny
   *
   * @return \DateTime
   */
  public function getNominateDeny(){
    return $this->nominateDeny;
  }
   
  /**
   * get nominateAdmit
   *
   * @return \DateTime
   */
  public function getNominateAdmit(){
    return $this->nominateAdmit;
  }
   
  /**
   * get acceptOffer
   *
   * @return \DateTime
   */
  public function getAcceptOffer(){
    return $this->acceptOffer;
  }
   
  /**
   * get decline offer
   *
   * @return \DateTime
   */
  public function getDeclineOffer(){
    return $this->declineOffer;
  }
   
  /**
   * get decisionLetterSent
   *
   * @return \DateTime
   */
  public function getDecisionLetterSent(){
    return $this->decisionLetterSent;
  }
   
  /**
   * get decisionLetterViewed
   *
   * @return \DateTime
   */
  public function getDecisionLetterViewed(){
    return $this->decisionLetterViewed;
  }
   
}