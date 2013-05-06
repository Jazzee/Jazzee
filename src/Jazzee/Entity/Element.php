<?php
namespace Jazzee\Entity;

/**
 * Element
 * Elements are the individual fields on a Page
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="elements", uniqueConstraints={
 *   @UniqueConstraint(name="element_fixedId", columns={"page_id", "fixedId"}),
 *   @UniqueConstraint(name="element_name", columns={"page_id", "name"})
 * })
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Element
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @ManyToOne(targetEntity="ElementType")
   */
  private $type;

  /**
   * @ManyToOne(targetEntity="Page",inversedBy="elements")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $page;

  /** @Column(type="integer") */
  private $weight;

  /** @Column(type="integer", nullable=true) */
  private $fixedId;

  /** @Column(type="string") */
  private $title;

  /** @Column(type="string", nullable=true) */
  private $name;

  /** @Column(type="string", nullable=true) */
  private $format;

  /** @Column(type="decimal", nullable=true) */
  private $min;

  /** @Column(type="decimal", nullable=true) */
  private $max;

  /** @Column(type="boolean") */
  private $required = false;

  /** @Column(type="text", nullable=true) */
  private $instructions;

  /** @Column(type="string", nullable=true) */
  private $defaultValue;

  /**
   * @OneToMany(targetEntity="ElementListItem",mappedBy="element")
   * @OrderBy({"weight" = "ASC"})
   */
  private $listItems;

  /**
   * @var \Jazzee\Interfaces\Element
   */
  private $jazzeeElement;

  public function __construct()
  {
    $this->listItems = new \Doctrine\Common\Collections\ArrayCollection();
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
   * Generate a Temporary id
   *
   * This should only be used when we need to termporarily generate an element
   * but have no intention of persisting it.  Use a string to be sure we cant persist
   */
  public function tempId()
  {
    $this->id = uniqid('element');
  }

  /**
   * Replace Page UUID
   * @PreUpdate
   *
   * When an element is modified it changes its parents UUID
   */
  public function replacePageUuid()
  {
    $this->page->replaceUuid();
  }

  /**
   * Set weight
   *
   * @param integer $weight
   */
  public function setWeight($weight)
  {
    $this->weight = $weight;
  }

  /**
   * Get weight
   *
   * @return integer $weight
   */
  public function getWeight()
  {
    return $this->weight;
  }

  /**
   * Set fixedId
   *
   * @param integer $fixedId
   */
  public function setFixedId($fixedId)
  {
    $this->fixedId = $fixedId;
  }

  /**
   * Get fixedId
   *
   * @return integer $fixedId
   */
  public function getFixedId()
  {
    return $this->fixedId;
  }

  /**
   * Set title
   *
   * @param string $title
   */
  public function setTitle($title)
  {
    $this->title = $title;
  }

  /**
   * Get title
   *
   * @return string $title
   */
  public function getTitle()
  {
    return $this->title;
  }

  /**
   * Set name
   *
   * @param string $value
   */
  public function setName($value)
  {
    if (empty($value)) {
      $this->name = null;
    } else {
      $this->name = preg_replace('#[^a-zA-Z0-9_]#', '', $value);
    }
  }

  /**
   * Get name
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set format
   *
   * @param string $format
   */
  public function setFormat($format)
  {
    if (empty($format)) {
      $format = null;
    }
    $this->format = $format;
  }

  /**
   * Get format
   *
   * @return string $format
   */
  public function getFormat()
  {
    return $this->format;
  }

  /**
   * Set min
   *
   * @param decimal $min
   */
  public function setMin($min)
  {
    if (empty($min)) {
      $min = null;
    }
    $this->min = $min;
  }

  /**
   * Get min
   *
   * @return decimal $min
   */
  public function getMin()
  {
    return $this->min;
  }

  /**
   * Set max
   *
   * @param decimal $max
   */
  public function setMax($max)
  {
    if (empty($max)) {
      $max = null;
    }
    $this->max = $max;
  }

  /**
   * Get max
   *
   * @return decimal $max
   */
  public function getMax()
  {
    return $this->max;
  }

  /**
   * Mark this element as required
   */
  public function required()
  {
    $this->required = true;
  }

  /**
   * Mark this element as optional
   */
  public function optional()
  {
    $this->required = false;
  }

  /**
   * Is this elemetn required
   * @return boolean $required
   */
  public function isRequired()
  {
    return $this->required;
  }

  /**
   * Set instructions
   *
   * @param text $instructions
   */
  public function setInstructions($instructions)
  {
    if (empty($instructions)) {
      $instructions = null;
    }
    $this->instructions = $instructions;
  }

  /**
   * Get instructions
   *
   * @return text $instructions
   */
  public function getInstructions()
  {
    return $this->instructions;
  }

  /**
   * Set defaultValue
   *
   * @param string $defaultValue
   */
  public function setDefaultValue($defaultValue)
  {
    if (empty($defaultValue)) {
      $defaultValue = null;
    }
    $this->defaultValue = $defaultValue;
  }

  /**
   * Get defaultValue
   *
   * @return string $defaultValue
   */
  public function getDefaultValue()
  {
    return $this->defaultValue;
  }

  /**
   * Set type
   *
   * @param Entity\ElementType $type
   */
  public function setType(ElementType $type)
  {
    $this->type = $type;
  }

  /**
   * Get type
   *
   * @return Entity\ElementType $type
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Set page
   *
   * @param Entity\Page $page
   */
  public function setPage(Page $page)
  {
    $this->page = $page;
  }

  /**
   * Get page
   *
   * @return Entity\Page $page
   */
  public function getPage()
  {
    return $this->page;
  }

  /**
   * Add list item
   *
   * @param Entity\ElementListItem $item
   */
  public function addItem(\Jazzee\Entity\ElementListItem $item)
  {
    $this->listItems[] = $item;
    if ($item->getElement() != $this) {
      $item->setElement($this);
    }
  }

  /**
   * Get listItems
   *
   * @return Doctrine\Common\Collections\Collection $listItems
   */
  public function getListItems()
  {
    return $this->listItems;
  }

  /**
   * Get list item by value
   *
   * @param string $value
   * @return Entity\ElementListItem $item
   */
  public function getItemByValue($value)
  {
    foreach ($this->listItems as $item) {
      if ($item->getValue() == $value) {
        return $item;
      }
    }

    return false;
  }

  /**
   * Get list item by name
   *
   * @param string $name
   * @return Entity\ElementListItem
   */
  public function getItemByName($name)
  {
    foreach ($this->listItems as $item) {
      if ($item->getName() == $name) {
        return $item;
      }
    }

    return false;
  }

  /**
   * Get list item by id
   *
   * @param integer $itemId
   * @return \Jazzee\Entity\ElementListItem
   */
  public function getItemById($itemId)
  {
    foreach ($this->listItems as $item) {
      if ($item->getId() == $itemId) {
        return $item;
      }
    }

    return false;
  }

  /**
   * Get the jazzeeElement
   *
   * @return \Jazzee\Interfaces\Element
   */
  public function getJazzeeElement()
  {
    if (is_null($this->jazzeeElement)) {
      $className = $this->type->getClass();
      $this->jazzeeElement = new $className($this);
      if (!($this->jazzeeElement instanceof \Jazzee\Interfaces\Element)) {
        throw new \Jazzee\Exception($this->type > getName() . ' has class ' . $className . ' that does not implement \Jazzee\Interfaces\Element interface');
      }
    }

    return $this->jazzeeElement;
  }
  
  /**
   * Convert the object to an array
   * 
   * @return array
   */
  public function toArray()
  {
    $arr = array();
    $arr['id'] = $this->id;
    $arr['defaultValue'] = $this->defaultValue;
    $arr['fixedId'] = $this->fixedId;
    $arr['format'] = $this->format;
    $arr['instructions'] = $this->instructions;
    $arr['max'] = $this->max;
    $arr['min'] = $this->min;
    $arr['name'] = $this->name;
    $arr['required'] = $this->required;
    $arr['title'] = $this->title;
    $arr['weight'] = $this->weight;

    return $arr;
  }

}