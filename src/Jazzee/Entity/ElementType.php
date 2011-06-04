<?php
namespace Jazzee\Entity;

/** 
 * ElementType
 * The ApplyElement class we are going to use for an element
 * @Entity 
 * @Table(name="element_types", 
 * uniqueConstraints={
 *   @UniqueConstraint(name="elementtype_class",columns={"class"}),
 *   @UniqueConstraint(name="elementtype_name",columns={"name"})
 *   })
 * @package    jazzee
 * @subpackage orm
 **/
class ElementType{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** @Column(type="string") */
  private $name;
  
  /** @Column(type="string") */
  private $class;

  /**
   * Get id
   *
   * @return bigint $id
   */
  public function getId(){
    return $this->id;
  }

  /**
   * Set name
   *
   * @param string $name
   */
  public function setName($name){
    $this->name = $name;
  }

  /**
   * Get name
   *
   * @return string $name
   */
  public function getName(){
    return $this->name;
  }

  /**
   * Set class
   *
   * @param string $class
   */
  public function setClass($class){
    $this->class = $class;
  }

  /**
   * Get class
   *
   * @return string $class
   */
  public function getClass(){
    return $this->class;
  }
}