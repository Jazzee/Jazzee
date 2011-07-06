<?php
namespace Jazzee\Entity;

/** 
 * Message
 * 
 * Threaded Applicant Messages
 * @Entity(repositoryClass="\Jazzee\Entity\MessageRepository")
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
   * @OneToOne(targetEntity="Message",inversedBy="reply")
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE") 
   */
  private $parent;
  
  /** 
   * @OneToOne(targetEntity="Message", mappedBy="parent")
   */
  private $reply;
  
  /** @Column(type="integer") */
  private $sender;

  /** @Column(type="string") */
  private $subject;

  /** @Column(type="text") */
  private $text;
  
  /** @Column(type="datetime") */
  private $createdAt;
  
  /** @Column(type="boolean") */
  private $isRead;
  
  public function __construct(){
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
   * Mark the lastUpdate automatically
   * @PrePersist
   */
  public function markLastUpdate(){
      $this->applicant->markLastUpdate();
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
   * Set subject
   *
   * @param string $subject
   */
  public function setSubject($subject){
    $this->subject = $subject;
  }

  /**
   * Get subject
   *
   * @return string $subject
   */
  public function getSubject(){
    return $this->subject;
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
   * @param integer $who
   * @return boolean $isRead
   */
  public function isRead($who){
    return($this->sender == $who or $this->isRead);
  }

  /**
   * Get read status of children
   *
   * @param integer $who
   * @return boolean
   */
  public function isReadThread($who){
    if(!$this->isRead($who)) return false;
    if(!$this->reply or !$this->reply->isReadThread($who)) return false;
    return true;
  }

  /**
   * Get last message in chain
   *
   * @return \Jazzee\Entity\Message
   */
  public function getLastMessage(){
    $message = $this;
    while($message->reply){
      $message = $message->reply;
    }
    return $message;
  }

  /**
   * Get first message in chain
   *
   * @return \Jazzee\Entity\Message
   */
  public function getFirstMessage(){
    $message = $this;
    while($message->parent){
      $message = $message->parent;
    }
    return $message;
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
   * @return Message
   */
  public function getReply(){
    return $this->reply;
  }
  
  /**
   * Add Reply
   * 
   * Add a reply to this message
   * @param \Jazzee\Entity\Message $reply
   */
  public function setReply(\Jazzee\Entity\Message $message){
    $this->reply = $message;
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

/**
 * Message Repository
 * Special Repository methods for Messages
 */
class MessageRepository extends \Doctrine\ORM\EntityRepository{
  
  /**
   * Find all the messages for an application
   * 
   * @param Application $application
   * @return Doctrine\Common\Collections\Collection $messages
   */
  public function findThreadByApplication(Application $application){
    $query = $this->_em->createQuery('SELECT m FROM Jazzee\Entity\Message m WHERE m.parent IS NULL AND m.applicant IN (SELECT a.id from \Jazzee\Entity\Applicant a WHERE a.application = :applicationId)');
    $query->setParameter('applicationId', $application->getId());
    return $query->getResult();
  }
}