<?php
namespace Entity;
/** 
 * Attachment
 * Attach a file to an applicant
 * @Entity @Table(name="attachments") 
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
   * @ManyToOne(targetEntity="Applicant",inversedBy="attachments",cascade={"all"})
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
}