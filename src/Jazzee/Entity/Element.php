<?php
namespace Jazzee\Entity;

/** 
 * Element
 * Elements are the individual fields on a Page
 * @Entity @Table(name="elements", uniqueConstraints={@UniqueConstraint(name="fixedId", columns={"page_id", "fixedId"})}) 
 * @package    jazzee
 * @subpackage orm
 **/
class Element{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** 
   * @ManyToOne(targetEntity="ElementType")
   * @JoinColumn(onUpdate="CASCADE") 
   */
  private $type;

  /** 
   * @ManyToOne(targetEntity="Page",inversedBy="elements")
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE") 
   */
  private $page;
  
  /** @Column(type="integer") */
  private $weight;
  
  /** @Column(type="integer", nullable=true) */
  private $fixedId;
  
  /** @Column(type="string") */
  private $title;
  
  /** @Column(type="string", nullable=true) */
  private $format;
  
  /** @Column(type="decimal", nullable=true) */
  private $min;
  
  /** @Column(type="decimal", nullable=true) */
  private $max;
  
  /** @Column(type="boolean") */
  private $required = false;
  
  /** @Column(type="text", nullable=true) */
  private $instructions;
  
  /** @Column(type="string", nullable=true) */
  private $defaultValue;
  
  /** 
   * @OneToMany(targetEntity="ElementListItem",mappedBy="element")
   */
  private $listItems;
  
  /**
   * @var \Jazzee\Element
   */
  private $jazzeeElement;
  

  public function __construct(){
    $this->listItems = new \Doctrine\Common\Collections\ArrayCollection();
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
   * Set weight
   *
   * @param integer $weight
   */
  public function setWeight($weight){
    $this->weight = $weight;
  }

  /**
   * Get weight
   *
   * @return integer $weight
   */
  public function getWeight(){
    return $this->weight;
  }

  /**
   * Set fixedId
   *
   * @param integer $fixedId
   */
  public function setFixedId($fixedId){
    $this->fixedId = $fixedId;
  }

  /**
   * Get fixedId
   *
   * @return integer $fixedId
   */
  public function getFixedId(){
    return $this->fixedId;
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
   * Set format
   *
   * @param string $format
   */
  public function setFormat($format){
    $this->format = $format;
  }

  /**
   * Get format
   *
   * @return string $format
   */
  public function getFormat(){
    return $this->format;
  }

  /**
   * Set min
   *
   * @param decimal $min
   */
  public function setMin($min){
    $this->min = $min;
  }

  /**
   * Get min
   *
   * @return decimal $min
   */
  public function getMin(){
    return $this->min;
  }

  /**
   * Set max
   *
   * @param decimal $max
   */
  public function setMax($max){
    $this->max = $max;
  }

  /**
   * Get max
   *
   * @return decimal $max
   */
  public function getMax(){
    return $this->max;
  }

  /**
   * Mark this element as required
   */
  public function required(){
    $this->required = true;
  }
  
  /**
   * Mark this element as optional
   */
  public function optional(){
    $this->required = false;
  }

  /**
   * Is this elemetn required
   * @return boolean $required
   */
  public function isRequired(){
    return $this->required;
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
   * Set defaultValue
   *
   * @param string $defaultValue
   */
  public function setDefaultValue($defaultValue){
    $this->defaultValue = $defaultValue;
  }

  /**
   * Get defaultValue
   *
   * @return string $defaultValue
   */
  public function getDefaultValue(){
    return $this->defaultValue;
  }

  /**
   * Set type
   *
   * @param Entity\ElementType $type
   */
  public function setType(ElementType $type){
    $this->type = $type;
  }

  /**
   * Get type
   *
   * @return Entity\ElementType $type
   */
  public function getType(){
    return $this->type;
  }

  /**
   * Set page
   *
   * @param Entity\Page $page
   */
  public function setPage(Page $page){
    $this->page = $page;
  }

  /**
   * Get page
   *
   * @return Entity\Page $page
   */
  public function getPage(){
    return $this->page;
  }

  /**
   * Get listItems
   *
   * @return Doctrine\Common\Collections\Collection $listItems
   */
  public function getListItems(){
    return $this->listItems;
  }
  
  /**
   * Get list item by value
   *
   * @param string $value
   * @return Entity\ElementListItem $item
   */
  public function getItemByValue($value){
    foreach($this->listItems as $item) if($item->getValue() == $value) return $item;
    return false;
  }
  
  /**
   * Get list item by id
   *
   * @param integer $id
   * @return Entity\ElementListItem $item
   */
  public function getItemById($id){
    foreach($this->listItems as $item) if($item->getId() == $id) return $item;
    return false;
  }
  
  /**
   * Get the jazzeeElement
   * 
   * @return \Jazzee\Element
   */
  public function getJazzeeElement(){
    if(is_null($this->jazzeeElement)){
      $class = $this->type->getClass();
      if(!class_exists($class)) $class = 'Jazzee\Element\TextInput';
      $this->jazzeeElement = new $class($this);
    }
    return $this->jazzeeElement;
  }
}