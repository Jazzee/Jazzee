<?php
namespace Entity;
/** 
 * Payment
 * Records all applicant payment attempts
 * @Entity @Table(name="payments") 
 * @package    jazzee
 * @subpackage orm
 **/
class Payment{
  /**
   * @Id 
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** 
   * @ManyToOne(targetEntity="Applicant",inversedBy="payments",cascade={"all"})
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE") 
   */
  private $applicant;
  
  /** 
   * @ManyToOne(targetEntity="PaymentType")
   * @JoinColumn(onUpdate="CASCADE") 
   */
  private $type;
  
  /** @Column(type="decimal") */
  private $amount;
  
  /** @Column(type="string") */
  private $status;
  
  /** 
   * @OneToMany(targetEntity="PaymentVariable", mappedBy="payment")
   */
  private $variables;
  
  /**
   * Define some string constants for the payment status
   */
  const PENDING = 'pending';
  const SETTLED = 'settled';
  const REJECTED = 'rejected';
  const REFUNDED = 'refunded';
  

  public function __construct(){
    $this->variables = new \Doctrine\Common\Collections\ArrayCollection();
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
   * Set amount
   *
   * @param decimal $amount
   */
  public function setAmount($amount){
    $this->amount = $amount;
  }

  /**
   * Get amount
   *
   * @return decimal $amount
   */
  public function getAmount(){
    return $this->amount;
  }

  /**
   * Get status
   *
   * @return string $status
   */
  public function getStatus(){
    return $this->status;
  }

  /**
   * Set type
   *
   * @param Entity\PaymentType $type
   */
  public function setType(PaymentType $type){
    $this->type = $type;
  }

  /**
   * Get type
   *
   * @return Entity\PaymentType $type
   */
  public function getType(){
    return $this->type;
  }

  /**
   * Add variable
   *
   * @param Entity\PaymentVariable $variable
   */
  public function addVariable(PaymentVariable $variable){
    $this->variables[] = $variable;
  }
  
  /**
   * Set a payment as pending
   */
  public function pending(){
    $this->status = self::PENDING;
  }
  
  /**
   * Set a payment as settled
   */
  public function settled(){
    $this->status = self::SETTLED;
  }
  
  /**
   * Set a payment as rejected
   */
  public function rejected(){
    $this->status = self::REJECTED;
  }
  
/**
   * Set a payment as refunded
   */
  public function refunded(){
    $this->status = self::REFUNDED;
  }
}