<?php
namespace Jazzee\Entity;

/**
 * Program
 * Represents a single program which contains Applications for Cycles
 * and Users with roles in the program
 *
 * @Entity(repositoryClass="Jazzee\Entity\ProgramRepository")
 * @Table(name="programs",
 * uniqueConstraints={
 *   @UniqueConstraint(name="program_name",columns={"name"}),
 *   @UniqueConstraint(name="program_shortname",columns={"shortName"})
 * })
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Program
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /** @Column(type="string") */
  private $name;

  /** @Column(type="string", length=32) */
  private $shortName;

  /** @Column(type="boolean") */
  private $isExpired = false;

  /**
   * Get the id
   * @return integer
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Get the name
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Get the shortName
   * @return string
   */
  public function getShortName()
  {
    return $this->shortName;
  }

  /**
   * Set the name
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * Set the shortName
   * @param string $shortName
   */
  public function setShortName($shortName)
  {
    $this->shortName = $shortName;
  }

  /**
   * Expire the program
   */
  public function expire()
  {
    $this->isExpired = true;
  }

  /**
   * UnExpire the program
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

}