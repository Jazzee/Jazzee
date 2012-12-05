<?php
namespace Jazzee\Entity;

/**
 * DisplayElement
 * Controls display variables for a single element
 *
 * @Entity
 * @Table(name="display_elements")
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class DisplayElement
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @ManyToOne(targetEntity="DisplayPage",inversedBy="elements")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $page;

  /** @Column(type="array") */
  private $attributes;

  /**
   * @ManyToOne(targetEntity="Element")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $element;

  public function __construct()
  {
    $this->attributes = array();
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
   * Get display page
   *
   * @return DisplayPage
   */
  public function getDisplayPage()
  {
    return $this->page;
  }

  /**
   * Set display page
   *
   * @param DisplayPage $page
   */
  public function setDisplayPage(DisplayPage $page)
  {
    $this->page = $page;
  }

  /**
   * Set attributes
   * @param array $attributes
   */
  public function setAttributes(array $attributes)
  {
    $this->attributes = $attributes;
  }

  /**
   * Get attributes
   *
   * @return string
   */
  public function getAttributes()
  {
    return $this->attributes;
  }

  /**
   * Get element
   *
   * @return Element
   */
  public function getElement()
  {
    return $this->element;
  }

  /**
   * Set element
   *
   * @param Element $element
   */
  public function setElement(Element $element)
  {
    $this->element = $element;
  }

}