<?php
namespace Jazzee\Entity;
/** 
 * PaymentVariable
 * Allow developers to store arbitrary data as a PaymentVariable so we don't need new tables for every new ApplyPayment type
 * @Entity
 * @HasLifecycleCallbacks 
 * @Table(name="payment_variables",uniqueConstraints={@UniqueConstraint(name="payment_variables", columns={"payment_id", "name"})}) 
 * @package    jazzee
 * @subpackage orm
 **/
class PaymentVariable{
  /**
   * @Id 
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** 
   * @ManyToOne(targetEntity="Payment", inversedBy="variables")
   * @JoinColumn(onDelete="CASCADE") 
   */
  private $payment;
  
  /** @Column(type="string") */
  private $name;
  
  /** @Column(type="text") */
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
   * Mark the lastUpdate automatically
   * @PrePersist @PreUpdate
   */
  public function markLastUpdate(){
      $this->payment->markLastUpdate();
  }

  /**
   * Set payment
   *
   * @param Entity\Payment $payment
   */
  public function setPayment(Payment $payment){
    $this->payment = $payment;
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
   * Get the base64 decoded value
   * @return blob
   */
  public function getValue(){
    return base64_decode($this->value);
  }
  
  /**
   * Base64 encode the value
   * @param mixed $value
   * @return mixed
   */
  public function setValue($value){
    return $this->value = base64_encode($value);
  }
}