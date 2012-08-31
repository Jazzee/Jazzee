<?php
namespace Jazzee\Entity;

/**
 * PaymentTypeVariable
 * Allow developers to store arbitrary data as a PaymentTypeVariable so we don't need new tables for every new ApplyPaymentType type
 *
 * @Entity @Table(name="payment_type_variables",uniqueConstraints={@UniqueConstraint(name="payment_type_variable_name", columns={"type_id", "name"})})
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class PaymentTypeVariable
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @ManyToOne(targetEntity="PaymentType", inversedBy="variables")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $type;

  /** @Column(type="string") */
  private $name;

  /** @Column(type="string") */
  private $value;

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
   * Set type
   *
   * @param Entity\PaymentType $type
   */
  public function setType($type)
  {
    $this->type = $type;
  }

  /**
   * Set name
   *
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * Get name
   *
   * @return string $name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Base64 encode the value
   * @param mixed $value
   * @return mixed
   */
  public function setValue($value)
  {
    return $this->value = base64_encode($value);
  }

  /**
   * Get the base64 decoded value
   * @return blob
   */
  public function getValue()
  {
    return base64_decode($this->value);
  }

}