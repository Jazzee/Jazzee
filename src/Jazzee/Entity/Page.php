<?php
namespace Jazzee\Entity;

/** 
 * Page
 * A page is not directly associated with an application - it can be a single case or a global page associated with many applications
 * @Entity @Table(name="pages") 
 * @package    jazzee
 * @subpackage orm
 **/
class Page{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** @Column(type="string") */
  private $title;
  
  /** 
   * @ManyToOne(targetEntity="PageType")
   * @JoinColumn(onDelete="SET NULL", onUpdate="CASCADE") 
   */
  private $type;
  
  /** @Column(type="boolean") */
  private $isGlobal = false;
  
  /** 
   * @ManyToOne(targetEntity="Page",inversedBy="children")
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE") 
   */
  private $parent;
  
  /** 
   * @OneToMany(targetEntity="Page", mappedBy="parent")
   */
  private $children;
  
  /** 
   * @OneToMany(targetEntity="PageVariable", mappedBy="page")
   */
  private $variables;

  /** 
   * @OneToMany(targetEntity="Element", mappedBy="page")
   * @OrderBy({"weight" = "ASC"})
   */
  private $elements;
  
  /** @Column(type="integer", nullable=true) */
  private $min;
  
  /** @Column(type="integer", nullable=true) */
  private $max;
  
  /** @Column(type="boolean") */
  private $isRequired = true;
  
  /** @Column(type="boolean") */
  private $answerStatusDisplay = false;
  
  /** @Column(type="text", nullable=true) */
  private $instructions;
  
  /** @Column(type="text", nullable=true) */
  private $leadingText;
  
  /** @Column(type="text", nullable=true) */
  private $trailingText;
  
  public function __construct(){
    $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    $this->variables = new \Doctrine\Common\Collections\ArrayCollection();
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
   * Generate a Temporary id
   *
   * This should only be used when we need to termporarily generate a page 
   * but have no intention of persisting it.  Use a string to be sure we cant persist
   */
  public function tempId(){
    $this->id = uniqid('page');
  }

  /**
   * Set title
   *
   * @param string $title
   */
  public function setTitle($title){
    $this->title = $title;
  }

  /**
   * Get title
   *
   * @return string $title
   */
  public function getTitle(){
    return $this->title;
  }

  /**
   * Make page global
   */
  public function makeGlobal(){
    $this->isGlobal = true;
  }
  
  /**
   * UnMake page global
   */
  public function notGlobal(){
    $this->isGlobal = false;
  }

  /**
   * Get Global status
   * @return boolean $isGlobal
   */
  public function isGlobal(){
    return $this->isGlobal;
  }

  /**
   * Set min
   *
   * @param integer $min
   */
  public function setMin($min){
    $this->min = $min;
  }

  /**
   * Get min
   *
   * @return integer $min
   */
  public function getMin(){
    return $this->min;
  }

  /**
   * Set max
   *
   * @param integer $max
   */
  public function setMax($max){
    $this->max = $max;
  }

  /**
   * Get max
   *
   * @return integer $max
   */
  public function getMax(){
    return $this->max;
  }

  /**
   * Make page required
   */
  public function required(){
    $this->isRequired = true;
  }
  
/**
   * Make page optional
   */
  public function optional(){
    $this->isRequired = false;
  }

  /**
   * Get required status
   * @return boolean $required
   */
  public function isRequired(){
    return $this->isRequired;
  }

  /**
   * Show the answer status
   */
  public function showAnswerStatus(){
    $this->answerStatusDisplay = true;
  }
  
/**
   * Hide the answer status
   */
  public function hideAnswerStatus(){
    $this->answerStatusDisplay = false;
  }

  /**
   * Should we show the answer status
   * @return boolean $showAnswerStatus
   */
  public function answerStatusDisplay(){
    return $this->answerStatusDisplay;
  }

  /**
   * Set instructions
   *
   * @param text $instructions
   */
  public function setInstructions($instructions){
    $this->instructions = $instructions;
  }

  /**
   * Get instructions
   *
   * @return text $instructions
   */
  public function getInstructions(){
    return $this->instructions;
  }

  /**
   * Set leadingText
   *
   * @param text $leadingText
   */
  public function setLeadingText($leadingText){
    $this->leadingText = $leadingText;
  }

  /**
   * Get leadingText
   *
   * @return text $leadingText
   */
  public function getLeadingText(){
    return $this->leadingText;
  }

  /**
   * Set trailingText
   *
   * @param text $trailingText
   */
  public function setTrailingText($trailingText){
    $this->trailingText = $trailingText;
  }

  /**
   * Get trailingText
   *
   * @return text $trailingText
   */
  public function getTrailingText(){
    return $this->trailingText;
  }

  /**
   * Set type
   *
   * @param Entity\PageType $type
   */
  public function setType(PageType $type){
    $this->type = $type;
  }

  /**
   * Get type
   *
   * @return Entity\PageType $type
   */
  public function getType(){
    return $this->type;
  }

  /**
   * Get parent
   *
   * @return Entity\Page $parent
   */
  public function getParent(){
    return $this->parent;
  }
  
  /**
   * Set parent
   *
   * @param Entity\Page $parent
   */
  public function setParent($parent){
    $this->parent = $parent;
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
   * Get a child by id
   *
   * @param integer $id
   * @return \Jazzee\Entity\Page
   */
  public function getChildById($id){
    foreach($this->children as $child) if($child->getId() == $id) return $child;
    return false;
  }
  
  /**
   * Set page variable
   * @param string $name
   * @param string $value
   */
  public function setVar($name, $value){
    foreach($this->variables as $variable)
      if($variable->getName() == $name)return $variable->setValue($value);
    //create a new empty variable with that name
    $var = new PageVariable;
    $var->setPage($this);
    $var->setName($name);
    $this->variables[] = $var;
    $var->setValue($value);
  }

  /**
   * get page variable
   * @param string $name
   * @return string $value
   */
  public function getVar($name){
    foreach($this->variables as $variable)
      if($variable->getName() == $name)return $variable->getValue();
  }
  /**
   * get page variables
   * @return array \Jazzee\Entity\PageVariable
   */
  public function getVariables(){
    return $this->variables;
  }

  /**
   * Add element
   *
   * @param Entity\Element $element
   */
  public function addElement(\Jazzee\Entity\Element $element){
    $this->elements[] = $element;
    if($element->getPage() != $this) $element->setPage($this);
  }

  /**
   * Get elements
   *
   * @return Doctrine\Common\Collections\Collection $elements
   */
  public function getElements(){
    return $this->elements;
  }
  
	/**
   * Get element by ID
   * @param integer $id
   * @return Entity\Element $element
   */
  public function getElementById($id){
    foreach($this->elements as $element) if($element->getId() == $id) return $element;
    return false;
  }
  
  /**
   * Get element by fixed ID
   * @param integer $id
   * @return Entity\Element $element
   */
  public function getElementByFixedId($id){
    foreach($this->elements as $element) if($element->getFixedId() == $id) return $element;
    return false;
  }
}