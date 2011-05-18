<?php
namespace Entity;
/** 
 * PageVariable
 * Allow developers to store arbitrary data as a PageVariable so we don't need new tables for every new ApplyPage type
 * @Entity @Table(name="page_variables",uniqueConstraints={@UniqueConstraint(name="page_variable", columns={"page_id", "value"})}) 
 * @package    jazzee
 * @subpackage orm
 **/
class PageVariable{
  /**
   * @Id 
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** 
   * @ManyToOne(targetEntity="Page", inversedBy="variables")
   * @JoinColumn(onDelete="CASCADE",onUpdate="CASCADE") 
   */
  private $page;
  
  /** @Column(type="string") */
  private $name;
  
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
   * Set value
   *
   * @param string $value
   */
  public function setValue($value){
    $this->value = base64_encode($value);
  }

  /**
   * Get value
   *
   * @return string $value
   */
  public function getValue(){
    return base64_decode($this->value);
  }
}