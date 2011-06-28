<?php 
namespace Jazzee\Entity;

/** 
 * AuditLog
 * AuditLog entries record critical user actions
 * @Entity
 * @Table(name="audit_log")
 * @package    jazzee
 * @subpackage orm
 **/

class AuditLog{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** @Column(type="datetime") */
  protected $createdAt;
  
  /** @Column(type="text") */
  private $text;
  
  /** 
   * @ManyToOne(targetEntity="User",inversedBy="logs")
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE") 
   */
  protected $user;
  
  public function __construct(){
    $this->createdAt = new \DateTime('now');
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
   * Set createdAt
   *
   * @param string $createdAt
   */
  public function setCreatedAt($createdAt){
    $this->createdAt = new \DateTime($createdAt);
  }

  /**
   * Get createdAt
   *
   * @return \DateTime $createdAt
   */
  public function getCreatedAt(){
    return $this->createdAt;
  }

  /**
   * Set user
   *
   * @param Entity\User $user
   */
  public function setUser(User $user){
    $this->user = $user;
  }

  /**
   * Get user
   *
   * @return Entity\User $user
   */
  public function getUser(){
    return $this->user;
  }

  /**
   * Set text
   *
   * @param string $text
   */
  public function setText($text){
    $this->text = $text;
  }

  /**
   * Get text
   *
   * @return string $text
   */
  public function getText(){
    return $this->text;
  }
}