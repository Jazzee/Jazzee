<?php
namespace Jazzee\Entity;

/**
 * PageType
 * The ApplyPage class we are going to use
 *
 * @Entity
 * @Table(name="page_types",
 * uniqueConstraints={
 *   @UniqueConstraint(name="pagetype_class",columns={"class"}),
 *   @UniqueConstraint(name="pagetype_name",columns={"name"})
 *   })
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class PageType
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

}
