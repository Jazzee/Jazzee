<?php
namespace Jazzee\Entity;

/**
 * DisplayPage
 * Controls display variables and elements for a single page
 *
 * @Entity
 * @Table(name="display_pages")
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class DisplayPage
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @ManyToOne(targetEntity="Display",inversedBy="pages")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $display;

  /** @Column(type="array") */
  private $attributes;

  /**
   * @ManyToOne(targetEntity="ApplicationPage")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $applicationPage;

  /**
   * @OneToMany(targetEntity="DisplayElement", mappedBy="page")
   */
  private $elements;

  public function __construct()
  {
    $this->elements = new \Doctrine\Common\Collections\ArrayCollection();
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
   * Get page
   *
   * @return Display
   */
  public function getDisplay()
  {
    return $this->display;
  }

  /**
   * Set display
   *
   * @param Display $display
   */
  public function setDisplay(Display $display)
  {
    $this->display = $display;
  }

  /**
   * Get page
   *
   * @return ApplicationPage
   */
  public function getApplicationPage()
  {
    return $this->applicationPage;
  }

  /**
   * Set page
   *
   * @param Page ApplicationPage
   */
  public function setApplicationPage(ApplicationPage $applicationPage)
  {
    $this->applicationPage = $applicationPage;
  }

  /**
   * Add element
   *
   * @param DisplayElement $element
   */
  public function addElement(DisplayElement $element)
  {
    $this->elements[] = $element;
    if ($element->getDisplayPage() != $this) {
      $element->setDisplayPage($this);
    }
  }

  /**
   * Get elements
   *
   * @return array DisplayElement
   */
  public function getElements()
  {
    return $this->elements;
  }

  /**
   * Get elements
   *
   * @return array DisplayElement
   */
  public function getElementIds()
  {
    $ids = array();
    foreach($this->elements as $element){
      $ids[] = $element->getElement()->getId();
    }
    return $ids;
  }


}