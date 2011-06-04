<?php
namespace Jazzee\Entity;

/** 
 * Message
 * Threaded Messages between Applicants and Users
 * @Entity 
 * @HasLifecycleCallbacks
 * @Table(name="messages") 
 * @package    jazzee
 * @subpackage orm
 **/
class Message{
  /**
   * Sender Types 
   */
  const APPLICANT = 2;
  const PROGRAM = 4;
  
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** 
   * @ManyToOne(targetEntity="Applicant",inversedBy="messages") 
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE") 
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
  
  /** @Column(type="integer") */
  private $sender;

  /** @Column(type="text") */
  private $text;
  
  /** @Column(type="datetime") */
  private $createdAt;
  
  /** @Column(type="boolean") */
  private $isRead;
  
  public function __construct(){
    $this->replies = new \Doctrine\Common\Collections\ArrayCollection();
    $this->isRead = false;
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
   * Set sender
   *
   * @param string $sender
   */
  public function setSender($sender){
    if(!in_array($sender, array(self::APPLICANT, self::PROGRAM))) throw new \Jazzee\Exception("Invalid sender type.  Must be one of the constants APPLICANT or PROGRAM");
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
   * Mark the created at automatically
   * @PrePersist
   * @param string $createdAt
   */
  public function markCreatedAt($createdAt = 'now'){
    $this->createdAt = new \DateTime($createdAt);
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
  
  /**
   * Add Reply
   * 
   * Add a reply to this message
   * @param \Jazzee\Entity\Message $reply
   */
  public function addReply(\Jazzee\Entity\Message $message){
    $this->replies[] = $message;
    if($message->getParent() !== $this) $message->setParent($this);
  }
  
  /**
   * Set Parent
   * 
   * Set the parent for this message
   * @param \Jazzee\Entity\Message $parent
   */
  public function setParent(\Jazzee\Entity\Message $parent){
    $this->parent = $parent;
  }
  
}