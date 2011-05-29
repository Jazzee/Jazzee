<?php
namespace Jazzee\Entity;

/** 
 * Message
 * Threaded Messages between Applicants and Users
 * @Entity @Table(name="messages") 
 * @package    jazzee
 * @subpackage orm
 **/
class Message{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** 
   * @ManyToOne(targetEntity="Applicant",inversedBy="messages") 
   * @JoinColumn(onDelete="SET NULL", onUpdate="CASCADE") 
   */
  private $applicant;
  
  /** 
   * @ManyToOne(targetEntity="Message",inversedBy="replies")
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE") 
   */
  private $parent;
  
  /** 
   * @OneToMany(targetEntity="Message", mappedBy="parent")
   */
  private $replies;
  
  /** @Column(type="string") */
  private $recipient;
  
  /** @Column(type="string") */
  private $sender;

  /** @Column(type="text") */
  private $text;
  
  /** @Column(type="datetime") */
  private $createdAt;
  
  /** @Column(type="boolean") */
  private $isRead;
  
  public function __construct(){
    $this->replies = new \Doctrine\Common\Collections\ArrayCollection();
  }
  
  /**
   * Get id
   *
   * @return bigint $id
   */
  public function getId(){
    return $this->id;
  }

  /**
   * Set recipient
   *
   * @param string $recipient
   */
  public function setRecipient($recipient){
    if(!in_array(strtolower($recipient), array('applicant', 'user'))) throw new Jazzee_Exception("{$recipient} is not a valid receipient");
    $this->recipient = $recipient;
  }

  /**
   * Get recipient
   *
   * @return string $recipient
   */
  public function getRecipient(){
    return $this->recipient;
  }

  /**
   * Set sender
   *
   * @param string $sender
   */
  public function setSender($sender){
    if(!in_array(strtolower($sender), array('applicant', 'user'))) throw new Jazzee_Exception("{$sender} is not a valid sender");
    $this->sender = $sender;
  }

  /**
   * Get sender
   *
   * @return string $sender
   */
  public function getSender(){
    return $this->sender;
  }

  /**
   * Set text
   *
   * @param text $text
   */
  public function setText($text){
    $this->text = $text;
  }

  /**
   * Get text
   *
   * @return text $text
   */
  public function getText(){
    return $this->text;
  }

  /**
   * Get createdAt
   *
   * @return datetime $createdAt
   */
  public function getCreatedAt(){
    return $this->createdAt;
  }

  
  /**
   * Mark as read
   */
  public function read(){
    $this->isRead = true;
  }
  
 /**
   * Un Mark as read
   */
  public function unRead(){
    $this->isRead = false;
  }

  /**
   * Get read status
   *
   * @return boolean $isRead
   */
  public function isRead(){
    return $this->isRead;
  }

  /**
   * Set applicant
   *
   * @param Entity\Applicant $applicant
   */
  public function setApplicant(Applicant $applicant){
    $this->applicant = $applicant;
  }

  /**
   * Get applicant
   *
   * @return Entity\Applicant $applicant
   */
  public function getApplicant(){
    return $this->applicant;
  }

  /**
   * Get parent
   *
   * @return Entity\Message $parent
   */
  public function getParent(){
    return $this->parent;
  }

  /**
   * Get replies
   *
   * @return Doctrine\Common\Collections\Collection $replies
   */
  public function getReplies(){
    return $this->replies;
  }
  
}