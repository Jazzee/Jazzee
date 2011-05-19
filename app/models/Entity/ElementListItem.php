<?php
namespace Entity;

/** 
 * ElementListItem
 * Elements like selects and checkboxes have list items
 * @Entity @Table(name="element_list_items") 
 * @package    jazzee
 * @subpackage orm
 **/
class ElementListItem{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** 
   * @ManyToOne(targetEntity="Element",inversedBy="listItems",cascade={"all"})
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE") 
   */
  private $element;
  
  /** @Column(type="integer") */
  private $weight;
  
  /** @Column(type="boolean") */
  private $active = true;
  
  /** @Column(type="string") */
  private $value;

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
   * Make item active
   */
  public function activate(){
    $this->active = true;
  }
  
 /**
   * Deactivate item
   */
  public function deactivate(){
    $this->active = false;
  }

  /**
   * Check if item is active
   * @return boolean $active
   */
  public function isActive(){
    return $this->active;
  }

  /**
   * Set value
   *
   * @param string $value
   */
  public function setValue($value){
    $this->value = $value;
  }

  /**
   * Get value
   *
   * @return string $value
   */
  public function getValue(){
    return $this->value;
  }
}