<?php
namespace Jazzee\Entity;

/**
 * Message
 *
 * Messages between applicants and programs
 *
 * @Entity
 * @Table(name="messages")
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Message
{
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
   * @ManyToOne(targetEntity="Thread",inversedBy="messages")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $thread;

  /** @Column(type="integer") */
  private $sender;

  /** @Column(type="text") */
  private $text;

  /** @Column(type="datetime") */
  private $createdAt;

  /** @Column(type="boolean") */
  private $isRead;

  public function __construct()
  {
    $this->isRead = false;
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
   * Set sender
   *
   * @param string $sender
   */
  public function setSender($sender)
  {
    if (!in_array($sender, array(self::APPLICANT, self::PROGRAM))) {
      throw new \Jazzee\Exception("Invalid sender type.  Must be one of the constants APPLICANT or PROGRAM");
    }
    $this->sender = $sender;
  }

  /**
   * Get sender
   *
   * @return string $sender
   */
  public function getSender()
  {
    return $this->sender;
  }

  /**
   * Set text
   *
   * @param text $text
   */
  public function setText($text)
  {
    $this->text = $text;
  }

  /**
   * Get text
   *
   * @return text $text
   */
  public function getText()
  {
    return $this->text;
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
   * @return datetime $createdAt
   */
  public function getCreatedAt()
  {
    return $this->createdAt;
  }

  /**
   * Mark as read
   */
  public function read()
  {
    $this->isRead = true;
  }

  /**
   * Un Mark as read
   */
  public function unRead()
  {
    $this->isRead = false;
  }

  /**
   * Get read status
   *
   * @param integer $sender who is the sender
   * @return boolean $isRead
   */
  public function isRead($sender)
  {
    if ($this->sender == $sender) {
      return $this->isRead;
    }

    return true;
  }

  /**
   * Set thread
   *
   * @param Entity\Thread $thread
   */
  public function setThread(Thread $thread)
  {
    $this->thread = $thread;
  }

  /**
   * Get thread
   *
   * @return \Jazzee\Entity\Thread $thread
   */
  public function getThread()
  {
    return $this->thread;
  }

}