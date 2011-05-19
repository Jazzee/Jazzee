<?php
namespace Entity;
/** 
 * PaymentType
 * The ApplyPayment class we are going to use
 * @Entity @Table(name="payment_types") 
 * @package    jazzee
 * @subpackage orm
 **/
class PaymentType{
  /**
   * @Id 
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** @Column(type="string") */
  private $name;
  
  /** @Column(type="string", unique=true) */
  private $class;
  
  /** 
   * @OneToMany(targetEntity="PaymentTypeVariable", mappedBy="type")
   */
  private $variables;
  
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

  /**
   * Add variable
   *
   * @param Entity\PaymentTypeVariable $variable
   */
  public function addVariable(PaymentTypeVariable $variable){
    $this->variables[] = $variable;
  }
}