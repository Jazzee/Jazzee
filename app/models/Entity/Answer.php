<?php 
namespace Entity;

/** 
 * Answer
 * Applicant answer to a page
 * @Entity @Table(name="answers") 
 * @package    jazzee
 * @subpackage orm
 **/

class Answer{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** 
   * @ManyToOne(targetEntity="Applicant",inversedBy="answers",cascade={"all"})
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE") 
   */
  protected $applicant;
  
  /** 
   * @ManyToOne(targetEntity="Page",cascade={"all"})
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE")
   */
  protected $page;
  
  /** 
   * @ManyToOne(targetEntity="Answer",inversedBy="children")
   * @JoinColumn(name="parent_id", referencedColumnName="id")
   */
  protected $parent;
  
  /** 
   * @OneToMany(targetEntity="Answer", mappedBy="parent")
   */
  protected $children;
  
  /** 
   * @OneToMany(targetEntity="ElementAnswer", mappedBy="answer")
   */
  protected $elements;
  
  /** 
   * @ManyToOne(targetEntity="AnswerStatusType")
   * @JoinColumn(onUpdate="CASCADE") 
   */
  protected $publicStatus;
  
  /** 
   * @ManyToOne(targetEntity="AnswerStatusType")
   * @JoinColumn(onUpdate="CASCADE") 
   */
  protected $privateStatus;
  
  /** @Column(type="text", nullable=true) */
  protected $attachment;
  
  /** @Column(type="string", length=255, unique=true) */
  protected $uniqueId;
  
  /** @Column(type="boolean") */
  protected $locked;
  
  /** @Column(type="datetime") */
  protected $updatedAt;
  
  public function __construct(){
    $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    $this->elements = new \Doctrine\Common\Collections\ArrayCollection();
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
   * Set attachment
   *
   * @param text $attachment
   */
  public function setAttachment($attachment){
    $this->attachment = $attachment;
  }

  /**
   * Get attachment
   *
   * @return text $attachment
   */
  public function getAttachment(){
    return $this->attachment;
  }

  /**
   * Set uniqueId
   *
   * @param string $uniqueId
   */
  public function setUniqueId($uniqueId){
    $this->uniqueId = $uniqueId;
  }

  /**
   * Get uniqueId
   *
   * @return string $uniqueId
   */
  public function getUniqueId(){
    return $this->uniqueId;
  }

  /**
   * Set locked
   *
   * @param boolean $locked
   */
  public function setLocked($locked){
    $this->locked = $locked;
  }

  /**
   * Get locked
   *
   * @return boolean $locked
   */
  public function getLocked(){
    return $this->locked;
  }

  /**
   * Set updatedAt
   *
   * @param datetime $updatedAt
   */
  public function setUpdatedAt($updatedAt){
    $this->updatedAt = $updatedAt;
  }

  /**
   * Get updatedAt
   *
   * @return datetime $updatedAt
   */
  public function getUpdatedAt(){
    return $this->updatedAt;
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
   * Set parent
   *
   * @param Entity\Answer $parent
   */
  public function setParent(Answer $parent){
    $this->parent = $parent;
  }

  /**
   * Get parent
   *
   * @return Entity\Answer $parent
   */
  public function getParent(){
    return $this->parent;
  }

  /**
   * Add children
   *
   * @param Entity\Answer $children
   */
  public function addChildren(Answer $children){
    $this->children[] = $children;
  }

  /**
   * Get children
   *
   * @return Doctrine\Common\Collections\Collection $children
   */
  public function getChildren(){
    return $this->children;
  }

  /**
   * Add elements
   *
   * @param Entity\ElementAnswer $element
   */
  public function addElementAnswer(ElementAnswer $element){
    $this->elements[] = $elements;
  }

  /**
   * Get elements
   *
   * @return Doctrine\Common\Collections\Collection $elements
   */
  public function getElementAnswers(){
    return $this->elements;
  }

  /**
   * Set publicStatus
   *
   * @param Entity\AnswerStatusType $publicStatus
   */
  public function setPublicStatus(AnswerStatusType $publicStatus){
    $this->publicStatus = $publicStatus;
  }

  /**
   * Get publicStatus
   *
   * @return Entity\AnswerStatusType $publicStatus
   */
  public function getPublicStatus(){
    return $this->publicStatus;
  }

  /**
   * Set privateStatus
   *
   * @param Entity\AnswerStatusType $privateStatus
   */
  public function setPrivateStatus(AnswerStatusType $privateStatus){
    $this->privateStatus = $privateStatus;
  }

  /**
   * Get privateStatus
   *
   * @return Entity\AnswerStatusType $privateStatus
   */
  public function getPrivateStatus(){
    return $this->privateStatus;
  }
}