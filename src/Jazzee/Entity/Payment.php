<?php
namespace Jazzee\Entity;

/**
 * Payment
 * Records all applicant payment attempts
 *
 * @Entity(repositoryClass="\Jazzee\Entity\PaymentRepository")
 * @Table(name="payments")
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Payment
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @OneToOne(targetEntity="Answer",inversedBy="payment")
   * @JoinColumn(onDelete="SET NULL")
   */
  private $answer;

  /**
   * @ManyToOne(targetEntity="PaymentType")
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

  public function __construct()
  {
    $this->variables = new \Doctrine\Common\Collections\ArrayCollection();
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
   * @return \Jazzee\Entity\Answer $answer
   */
  public function getAnswer()
  {
    return $this->answer;
  }

  /**
   * Set amount
   *
   * @param decimal $amount
   */
  public function setAmount($amount)
  {
    $this->amount = $amount;
  }

  /**
   * Get amount
   *
   * @return decimal $amount
   */
  public function getAmount()
  {
    return $this->amount;
  }

  /**
   * Get status
   *
   * @return string $status
   */
  public function getStatus()
  {
    return $this->status;
  }

  /**
   * Set type
   *
   * @param Entity\PaymentType $type
   */
  public function setType(PaymentType $type)
  {
    $this->type = $type;
  }

  /**
   * Get type
   *
   * @return Entity\PaymentType $type
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Get all the variables
   *
   * @return array \Jazzee\Entity\PaymentVarialbe
   */
  public function getVariables()
  {
    return $this->variables->toArray();
  }

  /**
   * Add variable
   *
   * @param Entity\PaymentVariable $variable
   */
  public function addVariable(PaymentVariable $variable)
  {
    $this->variables[] = $variable;
  }

  /**
   * Set a payment as pending
   */
  public function pending()
  {
    $this->status = self::PENDING;
  }

  /**
   * Set a payment as settled
   */
  public function settled()
  {
    $this->status = self::SETTLED;
  }

  /**
   * Set a payment as rejected
   */
  public function rejected()
  {
    $this->status = self::REJECTED;
  }

  /**
   * Set a payment as refunded
   */
  public function refunded()
  {
    $this->status = self::REFUNDED;
  }

  /**
   * Set payment variable
   * @param string $name
   * @param string $value
   */
  public function setVar($name, $value)
  {
    foreach ($this->variables as $variable) {
      if ($variable->getName() == $name) {
        return $variable->setValue($value);
      }
    }
    //create a new empty variable with that name
    $variable = new PaymentVariable();
    $variable->setPayment($this);
    $variable->setName($name);
    $variable->setValue($value);
    $this->variables[] = $variable;
  }

  /**
   * get payment variable
   * @param string $name
   * @return string $value
   */
  public function getVar($name)
  {
    foreach ($this->variables as $variable) {
      if ($variable->getName() == $name) {
        return $variable->getValue();
      }
    }
  }

}