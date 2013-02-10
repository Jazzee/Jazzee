<?php
namespace Jazzee\Entity;

/**
 * DisplayElement
 * Controls display variables for a single element
 *
 * @Entity
 * @Table(name="display_elements")
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class DisplayElement
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @Column(type="string")
   */
  private $type;

  /**
   * @Column(type="string")
   */
  private $title;

  /**
   * @Column(type="string", nullable=true)
   */
  private $name;

  /** @Column(type="integer") */
  private $weight;

  /**
   * @ManyToOne(targetEntity="Display",inversedBy="elements")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $display;

  /**
   * @ManyToOne(targetEntity="Element")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $element;

  public function __construct($type)
  {
    if(!in_array($type, array('applicant', 'page'))){
      throw new \Jazzee\Exception("{$type} is not a valid type for DisplayElements");
    }
    $this->type = $type;
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
   * Set weight
   *
   * @param integer $weight
   */
  public function setWeight($weight)
  {
    $this->weight = $weight;
  }

  /**
   * Get weight
   *
   * @return integer $weight
   */
  public function getWeight()
  {
    return $this->weight;
  }

  /**
   * Set title
   *
   * @param string $title
   */
  public function setTitle($title)
  {
    $this->title = $title;
  }

  /**
   * Get title
   *
   * @return string
   */
  public function getTitle()
  {
    return $this->title;
  }

  /**
   * Set name
   *
   * @param string $name
   */
  public function setName($name)
  {
    if($this->type != 'applicant'){
      throw new \Jazzee\Exception("You cannot set name for DisplayElements that do not have the type 'applicant'");
    }
    $this->name = $name;
  }

  /**
   * Get name
   *
   * @return string
   */
  public function getName()
  {
    switch($this->type){
      case 'applicant':
        return $this->name;
        break;
      case 'page':
        return $this->element->getId();
        break;
    }
    
    throw new \Jazzee\Exception("Cannot get name for {$this->type} DisplayElement type");
  }

  /**
   * Get type
   *
   * @return string $type
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Get element
   *
   * @return Element
   */
  public function getElement()
  {
    return $this->element;
  }

  /**
   * Set element
   *
   * @param Element $element
   */
  public function setElement(Element $element)
  {
    if($this->type != 'page'){
      throw new \Jazzee\Excption("You cannot set Element for DisplayElements that do not have the type 'element'");
    }
    $this->element = $element;
    $this->name = $this->element->getId();
  }

  /**
   * Get display
   *
   * @return \Jazzee\Entity\Display
   */
  public function getDisplay()
  {
    return $this->display;
  }
  
  /**
   * Set the display
   * 
   * @param \Jazzee\Entity\Display $display
   */
  public function setDisplay(\Jazzee\Entity\Display $display)
  {
    $this->display = $display;
  }

}