<?php
namespace Jazzee\Entity;

/**
 * PaymentType
 * The ApplyPayment class we are going to use
 *
 * @Entity
 * @Table(name="payment_types",
 * uniqueConstraints={
 *   @UniqueConstraint(name="payemnttype_class",columns={"class"}),
 *   @UniqueConstraint(name="paymenttype_name",columns={"name"})
 * })
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class PaymentType
{

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
   * @OneToMany(targetEntity="PaymentTypeVariable", mappedBy="type")
   */
  private $variables;

  /** @Column(type="boolean") */
  private $isExpired;

  /**
   * The Jazzee Payment Type
   * @var \Jazzee\PaymentType
   */
  private $jazzeePaymentType;

  public function __construct()
  {
    $this->isExpired = false;
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
   * Set class
   *
   * @param string $class
   */
  public function setClass($class)
  {
    $this->class = $class;
  }

  /**
   * Get class
   *
   * @return string $class
   */
  public function getClass()
  {
    return $this->class;
  }

  /**
   * get the varialbes
   *
   * @return array \Jazzee\Entity\PaymentTypeVariable
   */
  public function getVariables()
  {
    return $this->variables->toArray();
  }

  /**
   * Set variable
   * @param string $name
   * @param string $value
   */
  public function setVar($name, $value)
  {
    foreach ($this->variables as $variable) {
      if ($variable->getName() == $name) {
        return$variable->setValue($value);
      }
    }
    //create a new empty variable with that name
    $var = new PaymentTypeVariable();
    $var->setType($this);
    $var->setName($name);
    $var->setValue($value);
    $this->variables[] = $var;

    return $var;
  }

  /**
   * get payment type variable
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

  /**
   * Expire the type
   */
  public function expire()
  {
    $this->isExpired = true;
  }

  /**
   * UnExpire the type
   */
  public function unExpire()
  {
    $this->isExpired = false;
  }

  /**
   * Get expires status
   */
  public function isExpired()
  {
    return $this->isExpired;
  }

  /**
   * Get the pamentType class
   *
   * @param \Jazzee\Controller $controller
   * @return \Jazzee\PaymentType
   */
  public function getJazzeePaymentType(\Jazzee\Controller $controller)
  {
    if (is_null($this->jazzeePaymentType)) {
      $class = new $this->class($this, $controller);
      if (!($class instanceof \Jazzee\Interfaces\PaymentType)) {
        throw new \Jazzee\Exception($this->name . ' has class ' . $this->class . ' that does not implement \Jazzee\PaymentType interface');
      }
      $this->jazzeePaymentType = $class;
    }

    return $this->jazzeePaymentType;
  }

}