<?php
namespace Jazzee\Entity;

/**
 * CronVariable
 * Cron run variables
 *
 * @Entity
 * @Table(name="cron_variables",uniqueConstraints={@UniqueConstraint(name="cronvariable_name", columns={"name"})})
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class CronVariable
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /** @Column(type="string") */
  private $name;

  /** @Column(type="text") */
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
   * Set value
   *
   * @param string $value
   */
  public function setValue($value)
  {
    $this->value = base64_encode($value);
  }

  /**
   * Get value
   *
   * @return string $value
   */
  public function getValue()
  {
    return base64_decode($this->value);
  }

}