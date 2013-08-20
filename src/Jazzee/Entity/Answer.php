<?php
namespace Jazzee\Entity;

/**
 * Answer
 * Applicant answer to a page
 *
 * @Entity(repositoryClass="\Jazzee\Entity\AnswerRepository")
 * @Table(name="answers",
 * uniqueConstraints={@UniqueConstraint(name="answer_uniqueId",columns={"uniqueId"})})
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 * */
class Answer
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @ManyToOne(targetEntity="Applicant",inversedBy="answers")
   * @JoinColumn(onDelete="CASCADE")
   */
  protected $applicant;

  /**
   * @ManyToOne(targetEntity="Page")
   * @JoinColumn(onDelete="CASCADE")
   */
  protected $page;

  /**
   * @ManyToOne(targetEntity="Answer",inversedBy="children")
   * @JoinColumn(onDelete="CASCADE")
   */
  protected $parent;

  /**
   * @OneToMany(targetEntity="Answer", mappedBy="parent")
   */
  protected $children;

  /**
   * @OneToMany(targetEntity="ElementAnswer", mappedBy="answer", cascade={"persist"})
   */
  protected $elements;

  /**
   * @ManyToOne(targetEntity="AnswerStatusType")
   * @JoinColumn(onDelete="SET NULL")
   */
  protected $publicStatus;

  /**
   * @ManyToOne(targetEntity="AnswerStatusType")
   * @JoinColumn(onDelete="SET NULL")
   */
  protected $privateStatus;

  /** @Column(type="integer", nullable=true) */
  protected $pageStatus;

  /**
   * @OneToOne(targetEntity="Attachment",mappedBy="answer")
   */
  private $attachment;

  /** @Column(type="string", length=255) */
  protected $uniqueId;

  /** @Column(type="boolean") */
  protected $locked;

  /** @Column(type="datetime") */
  protected $updatedAt;

  /**
   * @ManyToOne(targetEntity="GREScore")
   */
  private $greScore;

  /**
   * @ManyToOne(targetEntity="TOEFLScore")
   */
  private $toeflScore;

  /**
   * @OneToOne(targetEntity="Payment", mappedBy="answer")
   */
  private $payment;

  /**
   * @ManyToOne(targetEntity="School")
   */
  private $school;

  /**
   * If we set a manual update don't override it
   * @var boolean
   */
  private $updatedAtOveridden = false;

  public function __construct()
  {
    $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    $this->elements = new \Doctrine\Common\Collections\ArrayCollection();
    $this->generateUniqueId();
    $this->locked = false;
    $this->updatedAt = new \DateTime();
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
   * Set attachment
   *
   * @param Attachment $attachment
   */
  public function setAttachment(Attachment $attachment)
  {
    $this->attachment = $attachment;
  }

  /**
   * Get attachment
   *
   * @return Attachment $attachment
   */
  public function getAttachment()
  {
    return $this->attachment;
  }

  /**
   * Set page
   *
   * @param Entity\Page $page
   */
  public function setPage($page)
  {
    $this->page = $page;
  }

  /**
   * Get page
   *
   * @return Entity\Page $page
   */
  public function getPage()
  {
    return $this->page;
  }

  /**
   * Generate a unique id
   */
  public function generateUniqueId()
  {
    //PHPs uniquid function is time based and therefor guessable
    //A stright random MD5 sum is too long for email and tends to line break causing usability problems
    //So we get unique through uniquid and we get random by prefixing it with a part of an MD5
    //hopefully this results in a URL friendly short, but unguessable string
    $prefix = substr(md5(mt_rand() * mt_rand()), rand(0, 24), rand(6, 8));
    $this->uniqueId = \uniqid($prefix);
  }

  /**
   * Set a uniqueid
   * Prefferably call generateUniqueId - but it can also be set manually
   * @param string $uniqueId;
   */
  public function setUniqueId($uniqueId)
  {
    $this->uniqueId = $uniqueId;
  }

  /**
   * Get uniqueId
   *
   * @return string $uniqueId
   */
  public function getUniqueId()
  {
    return $this->uniqueId;
  }

  /**
   * Lock the Answer
   */
  public function lock()
  {
    $this->locked = true;
  }

  /**
   * UnLock the Answer
   */
  public function unlock()
  {
    $this->locked = false;
  }

  /**
   * Get locked status
   *
   * @return boolean $locked
   */
  public function isLocked()
  {
    return $this->locked;
  }

  /**
   * Set updatedAt
   *
   * @param string $updatedAt
   */
  public function setUpdatedAt($updatedAt)
  {
    $this->updatedAtOveridden = true;
    $this->updatedAt = new \DateTime($updatedAt);
  }

  /**
   * Get updatedAt
   *
   * @return DateTime $updatedAt
   */
  public function getUpdatedAt()
  {
    return $this->updatedAt;
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
   * @return \Jazzee\Entity\Applicant $applicant
   */
  public function getApplicant()
  {
    return $this->applicant;
  }

  /**
   * Set parent
   *
   * @param Entity\Answer $parent
   */
  public function setParent(Answer $parent)
  {
    $this->parent = $parent;
  }

  /**
   * Get parent
   *
   * @return Entity\Answer $parent
   */
  public function getParent()
  {
    return $this->parent;
  }

  /**
   * Add children
   *
   * @param Entity\Answer $child
   */
  public function addChild(Answer $child)
  {
    $this->children[] = $child;
    if ($child->getParent() != $this) {
      $child->setParent($this);
    }
    if ($child->getApplicant() != $this->applicant) {
      $child->setApplicant($this->applicant);
    }
  }

  /**
   * Get children
   *
   * @return Doctrine\Common\Collections\Collection $children
   */
  public function getChildren()
  {
    return $this->children;
  }

  /**
   * Get Child for Page
   *
   * @param \Jazzee\Entity\Page $page
   * @return \Jazzee\Entity\Answer
   */
  public function getChildByPage(Page $page)
  {
    foreach ($this->children as $child) {
      if ($child->getPage() == $page) {
        return $child;
      }
    }

    return false;
  }

  /**
   * Add elements
   *
   * @param Entity\ElementAnswer $element
   */
  public function addElementAnswer(ElementAnswer $element)
  {
    $this->elements[] = $element;
    if ($element->getAnswer() != $this) {
      $element->setAnswer($this);
    }
  }

  /**
   * Get elements
   *
   * @return Doctrine\Common\Collections\Collection $elements
   */
  public function getElementAnswers()
  {
    return $this->elements;
  }

  /**
   * Get ElementAnswers for Element
   *
   * @param \Jazzee\Entity\Element $element
   * @return array
   */
  public function getElementAnswersForElement(Element $element)
  {
    $arr = array();
    foreach ($this->elements as $elementAnswer) {
      if ($elementAnswer->getElement() === $element) {
        $arr[] = $elementAnswer;
      }
    }

    return new \Doctrine\Common\Collections\ArrayCollection($arr);
  }

  /**
   * Get ElementAnswers for Element by position
   *
   * @param \Jazzee\Entity\Element $element
   * @param integer $position
   * @return ElementAnswer
   */
  public function getElementAnswersForElementByPosition(Element $element, $position)
  {
    foreach ($this->getElementAnswersForElement($element) as $elementAnswer) {
      if ($elementAnswer->getPosition() == $position) {
        return $elementAnswer;
      }
    }
    return false;
  }

  /**
   * Set publicStatus
   *
   * @param Entity\AnswerStatusType $publicStatus
   */
  public function setPublicStatus(AnswerStatusType $publicStatus)
  {
    $this->publicStatus = $publicStatus;
  }

  /**
   * Get publicStatus
   *
   * @return Entity\AnswerStatusType $publicStatus
   */
  public function getPublicStatus()
  {
    return $this->publicStatus;
  }

  /**
   * Clear Public Status
   */
  public function clearPublicStatus()
  {
    $this->publicStatus = null;
  }

  /**
   * Set privateStatus
   *
   * @param Entity\AnswerStatusType $privateStatus
   */
  public function setPrivateStatus(AnswerStatusType $privateStatus)
  {
    $this->privateStatus = $privateStatus;
  }

  /**
   * Get privateStatus
   *
   * @return Entity\AnswerStatusType $privateStatus
   */
  public function getPrivateStatus()
  {
    return $this->privateStatus;
  }

  /**
   * Clear Private Status
   */
  public function clearPrivateStatus()
  {
    $this->privateStatus = null;
  }

  /**
   * Mark the lastUpdate automatically
   */
  public function markLastUpdate()
  {
    if (!$this->updatedAtOveridden) {
      $this->updatedAt = new \DateTime();
    }
  }

  /**
   * get payment
   *
   * @return \Jazzee\Entity\Payment $payment
   */
  public function getPayment()
  {
    return $this->payment;
  }

  /**
   * Set payment
   *
   * @param \Jazzee\Entity\Payment $payment
   */
  public function setPayment(\Jazzee\Entity\Payment $payment)
  {
    $this->payment = $payment;
    if ($payment->getAnswer() != $this) {
      $payment->setAnswer($this);
    }
  }

  /**
   * get gre score
   *
   * @return \Jazzee\Entity\GREScore $score
   */
  public function getGREScore()
  {
    return $this->greScore;
  }

  /**
   * Set gre score
   *
   * @param \Jazzee\Entity\GREScore $score
   */
  public function setGREScore($score)
  {
    $this->greScore = $score;
  }

  /**
   * get toefl score
   *
   * @return \Jazzee\Entity\TOEFL $score
   */
  public function getTOEFLScore()
  {
    return $this->toeflScore;
  }

  /**
   * Set toefl score
   *
   * @param \Jazzee\Entity\TOEFLScore $score
   */
  public function setTOEFLScore($score)
  {
    $this->toeflScore = $score;
  }
  
  /**
   * Set the school
   * @param \Jazzee\Entity\School $school
   */
  public function setSchool(School $school)
  {
    $this->school = $school;
  }
  
  /**
   * Remove the school
   */
  public function removeSchool()
  {
    $this->school = null;
  }
  
  /**
   * Get the school
   * 
   * @return \Jazzee\Entity\School
   */
  public function getSchool()
  {
    return $this->school;
  }

  /**
   * Conviently get a matched score
   *
   * Try both GRE and TOEFL and grab a match from wherever it might be
   *
   * @return \Jazzee\Entity\GREScore | \Jazzee\Entity\TOEFLScore | false if no score is present
   */
  public function getMatchedScore()
  {
    if ($this->greScore) {
      return $this->greScore;
    }
    if ($this->toeflScore) {
      return $this->toeflScore;
    }

    return false;
  }

  /**
   * Set page status
   *
   * @param integer $pageStatus
   */
  public function setPageStatus($pageStatus)
  {
    $this->pageStatus = $pageStatus;
  }

  /**
   * Get the page status
   *
   * @return integer $pageStatus
   */
  public function getPageStatus()
  {
    return $this->pageStatus;
  }

}