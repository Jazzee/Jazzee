<?php
namespace Jazzee\Entity;

/**
 * Thread
 * Threads are containers for messages
 *
 * @Entity(repositoryClass="\Jazzee\Entity\ThreadRepository")
 * @Table(name="threads")
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Thread
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @ManyToOne(targetEntity="Applicant",inversedBy="threads")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $applicant;

  /**
   * @OneToMany(targetEntity="Message",mappedBy="thread")
   * @OrderBy({"createdAt" = "ASC"})
   */
  private $messages;

  /** @Column(type="string") */
  private $subject;

  /** @Column(type="datetime") */
  private $createdAt;

  public function __construct()
  {
    $this->messages = new \Doctrine\Common\Collections\ArrayCollection();
    $this->createdAt = new \DateTime();
  }

  /**
   * Get id
   *
   * @return bigint $id
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set subject
   *
   * @param string $subject
   */
  public function setSubject($subject)
  {
    $this->subject = $subject;
  }

  /**
   * Get subject
   *
   * @return string $subject
   */
  public function getSubject()
  {
    return $this->subject;
  }

  /**
   * Set createdAt
   *
   * @param string $createdAt
   */
  public function setCreatedAt($createdAt)
  {
    $this->createdAt = new \DateTime($createdAt);
  }

  /**
   * Get createdAt
   *
   * @return \DateTime $createAt
   */
  public function getCreatedAt()
  {
    return $this->createdAt;
  }

  /**
   * Set applicant
   *
   * @param Entity\Applicant $applicant
   */
  public function setApplicant(Applicant $applicant)
  {
    $this->applicant = $applicant;
  }

  /**
   * Get applicant
   *
   * @return Entity\Applicant $applicant
   */
  public function getApplicant()
  {
    return $this->applicant;
  }

  /**
   * Add Message
   *
   * Add a message to this thread
   * @param \Jazzee\Entity\Message $message
   */
  public function addMessage(\Jazzee\Entity\Message $message)
  {
    $this->messages[] = $message;
    if ($message->getThread() !== $this) {
      $message->setThread($this);
    }
  }

  /**
   * Check for an unread message in the thread
   *
   * @param integer $sender
   * @return boolean
   */
  public function hasUnreadMessage($sender)
  {
    foreach ($this->messages as $message) {
      if (!$message->isRead($sender)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Get messages
   *
   * @return Doctrine\Common\Collections\Collection $messages
   */
  public function getMessages()
  {
    return $this->messages;
  }

  /**
   * Get last unreadMessage
   *
   * @param integer $sender
   * @return Message $message
   */
  public function getLastUnreadMessage($sender)
  {
    if (!$this->hasUnreadMessage($sender)) {
      return false;
    }
    $lastMessage = false;
    foreach ($this->messages as $message) {
      if (!$message->isRead($sender)) {
        $lastMessage = $message;
      }
    }

    return $lastMessage;
  }

  /**
   * Get first message
   *
   * @return Message $message
   */
  public function getFirstMessage()
  {
    return $this->messages->first();
  }

  /**
   * Get first message
   *
   * @return Message $message
   */
  public function getLastMessage()
  {
    return $this->messages->last();
  }

  /**
   * Get the unread message count
   *
   * @param integer $sender
   * @return integer $count
   */
  public function getUnreadMessageCount($sender)
  {
    if (!$this->hasUnreadMessage($sender)) {
      return 0;
    }
    $count = 0;
    foreach ($this->messages as $message) {
      if (!$message->isRead($sender)) {
        $count++;
      }
    }

    return $count;
  }

  /**
   * Get the message count
   *
   * @return integer $count
   */
  public function getMessageCount()
  {
    return $this->messages->count();
  }

}