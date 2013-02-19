<?php
namespace Jazzee\Entity;

/**
 * ElementAnswer
 * Break down the response from each Element on a Page into an ElementAnswer
 * In cases where there are multiple answers (like checkboxes) a single answer gets multiple rows by position
 *
 * @Entity(repositoryClass="\Jazzee\Entity\ElementAnswerRepository")
 * @HasLifecycleCallbacks
 * @Table(name="element_answers")
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ElementAnswer
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @ManyToOne(targetEntity="Answer",inversedBy="elements")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $answer;

  /**
   * @ManyToOne(targetEntity="Element")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $element;

  /** @Column(type="integer", nullable=true) */
  private $position;

  /** @Column(type="string", length=255, nullable=true) */
  private $eShortString;

  /** @Column(type="text", nullable=true) */
  private $eText;

  /** @Column(type="datetime", nullable=true) */
  private $eDate;

  /** @Column(type="integer", nullable=true) */
  private $eInteger;

  /** @Column(type="decimal", nullable=true) */
  private $eDecimal;

  /** @Column(type="text", nullable=true) */
  private $eBlob;

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
   * Set position
   *
   * @param integer $position
   */
  public function setPosition($position)
  {
    $this->position = $position;
  }

  /**
   * Get position
   *
   * @return integer $position
   */
  public function getPosition()
  {
    return $this->position;
  }

  /**
   * Set eShortString
   *
   * @param string $eShortString
   */
  public function setEShortString($eShortString)
  {
    $this->eShortString = $eShortString;
  }

  /**
   * Get eShortString
   *
   * @return string $eShortString
   */
  public function getEShortString()
  {
    return $this->eShortString;
  }

  /**
   * Set eText
   *
   * @param text $eText
   */
  public function setEText($eText)
  {
    $this->eText = $eText;
  }

  /**
   * Get eText
   *
   * @return text $eText
   */
  public function getEText()
  {
    return $this->eText;
  }

  /**
   * Set eDate
   *
   * @param string $eDate
   */
  public function setEDate($eDate)
  {
    $this->eDate = new \DateTime($eDate);
  }

  /**
   * Get eDate
   *
   * @return \DateTime $eDate
   */
  public function getEDate()
  {
    return $this->eDate;
  }

  /**
   * Set eInteger
   *
   * @param integer $eInteger
   */
  public function setEInteger($eInteger)
  {
    $this->eInteger = $eInteger;
  }

  /**
   * Get eInteger
   *
   * @return integer $eInteger
   */
  public function getEInteger()
  {
    return $this->eInteger;
  }

  /**
   * Set eDecimal
   *
   * @param decimal $eDecimal
   */
  public function setEDecimal($eDecimal)
  {
    $this->eDecimal = $eDecimal;
  }

  /**
   * Get eDecimal
   *
   * @return decimal $eDecimal
   */
  public function getEDecimal()
  {
    return $this->eDecimal;
  }

  /**
   * Set eBlob
   *
   * @param text $eBlob
   */
  public function setEBlob($blob)
  {
    $this->eBlob = base64_encode($blob);
  }

  /**
   * Get eBlob
   *
   * @return text $eBlob
   */
  public function getEBlob()
  {
    return base64_decode($this->eBlob);
  }

  /**
   * Set element
   *
   * @param \Jazzee\Entity\Element $element
   */
  public function setElement(Element $element)
  {
    $this->element = $element;
  }

  /**
   * Get element
   *
   * @return \Jazzee\Entity\Element
   */
  public function getElement()
  {
    return $this->element;
  }

  /**
   * Set answer
   *
   * @param \Jazzee\Entity\Answer $answer
   */
  public function setAnswer(Answer $answer)
  {
    $this->answer = $answer;
  }
  
  /**
   * Get answer
   * 
   * @return \Jazzee\Entity\Answer
   */
  public function getAnswer()
  {
    return $this->answer;
  }

  /**
   * Ensure any special elemetn actions get taken
   * @PreRemove
   */
  public function preRemove()
  {
    $this->element->getJazzeeElement()->removeElementAnswer($this);
  }

}