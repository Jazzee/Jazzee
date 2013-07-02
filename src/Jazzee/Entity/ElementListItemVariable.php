<?php
namespace Jazzee\Entity;

/**
 * ElementListItemVariable
 * Allow developers to store arbitrary data as a ElementListItemVariable
 *
 * @Entity
 * @Table(name="element_list_item_variables",uniqueConstraints={@UniqueConstraint(name="elementlistitemvariable_name", columns={"item_id", "name"})})
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ElementListItemVariable
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @ManyToOne(targetEntity="ElementListItem", inversedBy="variables")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $item;

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
   * Set item
   *
   * @param Jazzee\Entity\ElementListItem $item
   */
  public function setItem(\Jazzee\Entity\ElementListItem $item)
  {
    $this->item = $item;
  }

  /**
   * Get item
   *
   * @return Jazzee\Entity\ElementListItem $item
   */
  public function getItem()
  {
    return $thhis->item;
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
    $this->value = $value;
  }

  /**
   * Get value
   *
   * @return string $value
   */
  public function getValue()
  {
    return $this->value;
  }

}