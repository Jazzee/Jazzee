<?php
namespace Jazzee\Entity;
/** 
 * Attachment
 * Attach a file to an applicant
 * @Entity
 * @HasLifecycleCallbacks 
 * @Table(name="attachments") 
 * @package    jazzee
 * @subpackage orm
 **/
class Attachment{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** 
   * @ManyToOne(targetEntity="Applicant",inversedBy="attachments")
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE") 
   */
  private $applicant;
  
  /** @Column(type="text", nullable=true) */
  private $attachment;
  
/**
   * Get id
   *
   * @return bigint $id
   */
  public function getId(){
    return $this->id;
  }
  
  /**
   * Set the Applicant
   * @param Entity\Applicant $applicant
   */
  public function setApplicant(Applicant $applicant){
    $this->applicant = $applicant;
  }

  /**
   * Convert the attachment to base64 and store it
   *
   * @param blob $attachment
   */
  public function setAttachment($blob){
    $this->attachment = base64_encode($blob);
  }

  /**
   * Get attachment
   *
   * @return text $attachment
   */
  public function getAttachment(){
    return base64_decode($this->attachment);
  }
  
  /**
   * Mark the lastUpdate automatically 
   * @PrePersist
   */
  public function markLastUpdate(){
      $this->applicant->markLastUpdate();
  }
}