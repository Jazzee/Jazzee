<?php
namespace Jazzee\Entity;

/**
 * Page
 * A page is not directly associated with an application - it can be a single case or a global page associated with many applications
 *
 * @Entity(repositoryClass="\Jazzee\Entity\PageRepository")
 * @HasLifecycleCallbacks
 * @Table(name="pages",
 * uniqueConstraints={@UniqueConstraint(name="page_uuid",columns={"uuid"})})
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Page
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /** @Column(type="string") */
  private $uuid;

  /** @Column(type="string") */
  private $title;

  /**
   * @ManyToOne(targetEntity="PageType")
   */
  private $type;

  /** @Column(type="boolean") */
  private $isGlobal;

  /**
   * @ManyToOne(targetEntity="Page",inversedBy="children")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $parent;

  /** @Column(type="integer", nullable=true) */
  private $fixedId;

  /**
   * @OneToMany(targetEntity="Page", mappedBy="parent")
   */
  private $children;

  /**
   * @OneToMany(targetEntity="PageVariable", mappedBy="page")
   */
  private $variables;

  /**
   * @OneToMany(targetEntity="Element", mappedBy="page")
   * @OrderBy({"weight" = "ASC"})
   */
  private $elements;

  /** @Column(type="integer", nullable=true) */
  private $min;

  /** @Column(type="integer", nullable=true) */
  private $max;

  /** @Column(type="boolean") */
  private $isRequired;

  /** @Column(type="boolean") */
  private $answerStatusDisplay;

  /** @Column(type="text", nullable=true) */
  private $instructions;

  /** @Column(type="text", nullable=true) */
  private $leadingText;

  /** @Column(type="text", nullable=true) */
  private $trailingText;

  /**
   * @OneToMany(targetEntity="ApplicationPage", mappedBy="page")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $applicationPages;

  /**
   * a Generic application Jazzee page we store it so it can be persistent
   * @var \Jazzee\Interfaces\Page
   */
  private $_applicationPageJazzeePage;

  /**
   * a Generic application page we store it so doesn't get recreated
   * @var \Jazzee\Entity\ApplicationPage
   */
  private $_fakeApplicationPage;

  public function __construct()
  {
    $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    $this->variables = new \Doctrine\Common\Collections\ArrayCollection();
    $this->elements = new \Doctrine\Common\Collections\ArrayCollection();
    $this->applicationPages = new \Doctrine\Common\Collections\ArrayCollection();
    $this->isGlobal = false;
    $this->isRequired = true;
    $this->answerStatusDisplay = false;
    $this->replaceUuid();
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
   * Get uuid
   *
   * @return string $uuid
   */
  public function getUuid()
  {
    return $this->uuid;
  }

  /**
   * Set uuid
   *
   * @param string $uuid
   */
  public function setUuid($uuid)
  {
    $this->uuid = $uuid;
  }

  /**
   * Generate a Temporary id
   *
   * This should only be used when we need to termporarily generate a page
   * but have no intention of persisting it.  Use a string to be sure we cant persist
   */
  public function tempId()
  {
    $this->id = uniqid('page');
  }

  /**
   * Replace UUID
   * @PreUpdate
   * UUIDs are designed to be permanent.
   * You should only replace it if the page is being modified
   */
  public function replaceUuid()
  {
    $this->uuid = \Foundation\UUID::v4();
    if ($this->parent) {
      $this->parent->replaceUuid();
    }
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
   * Make page global
   */
  public function makeGlobal()
  {
    $this->isGlobal = true;
  }

  /**
   * UnMake page global
   */
  public function notGlobal()
  {
    $this->isGlobal = false;
  }

  /**
   * Get Global status
   * @return boolean $isGlobal
   */
  public function isGlobal()
  {
    return $this->isGlobal;
  }

  /**
   * Set min
   *
   * @param integer $min
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
   * @return integer $min
   */
  public function getMin()
  {
    return $this->min;
  }

  /**
   * Set max
   *
   * @param integer $max
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
   * @return integer $max
   */
  public function getMax()
  {
    return $this->max;
  }

  /**
   * Make page required
   */
  public function required()
  {
    $this->isRequired = true;
  }

  /**
   * Make page optional
   */
  public function optional()
  {
    $this->isRequired = false;
  }

  /**
   * Get required status
   * @return boolean $required
   */
  public function isRequired()
  {
    return $this->isRequired;
  }

  /**
   * Show the answer status
   */
  public function showAnswerStatus()
  {
    $this->answerStatusDisplay = true;
  }

  /**
   * Hide the answer status
   */
  public function hideAnswerStatus()
  {
    $this->answerStatusDisplay = false;
  }

  /**
   * Should we show the answer status
   * @return boolean $showAnswerStatus
   */
  public function answerStatusDisplay()
  {
    return $this->answerStatusDisplay;
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
   * Set leadingText
   *
   * @param text $leadingText
   */
  public function setLeadingText($leadingText)
  {
    if (empty($leadingText)) {
      $leadingText = null;
    }
    $this->leadingText = $leadingText;
  }

  /**
   * Get leadingText
   *
   * @return text $leadingText
   */
  public function getLeadingText()
  {
    return $this->leadingText;
  }

  /**
   * Set trailingText
   *
   * @param text $trailingText
   */
  public function setTrailingText($trailingText)
  {
    if (empty($trailingText)) {
      $trailingText = null;
    }
    $this->trailingText = $trailingText;
  }

  /**
   * Get trailingText
   *
   * @return text $trailingText
   */
  public function getTrailingText()
  {
    return $this->trailingText;
  }

  /**
   * Set type
   *
   * @param Entity\PageType $type
   */
  public function setType(PageType $type)
  {
    $this->type = $type;
  }

  /**
   * Get type
   *
   * @return Entity\PageType $type
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Get parent
   *
   * @return Entity\Page $parent
   */
  public function getParent()
  {
    return $this->parent;
  }

  /**
   * Set parent
   *
   * @param Entity\Page $parent
   */
  public function setParent($parent)
  {
    $this->parent = $parent;
  }

  /**
   * Add Child
   *
   * @param \Jazzee\Entity\Page $page
   */
  public function addChild(\Jazzee\Entity\Page $page)
  {
    $this->children[] = $page;
    if ($page->getParent() != $this) {
      $page->setParent($this);
    }
  }

  /**
   * Get children
   *
   * @return Doctrine\Common\Collections\Collection $children
   */
  public function getChildren()
  {
    return $this->children;
  }

  /**
   * Get a child by id
   *
   * @param integer $childId
   * @return \Jazzee\Entity\Page
   */
  public function getChildById($childId)
  {
    foreach ($this->children as $child) {
      if ($child->getId() == $childId) {
        return $child;
      }
    }

    return false;
  }

  /**
   * Get a child by fixedId
   *
   * @param integer $childId
   * @return \Jazzee\Entity\Page
   */
  public function getChildByFixedId($fixedId)
  {
    foreach ($this->children as $child) {
      if ($child->getFixedId() == $fixedId) {
        return $child;
      }
    }

    return false;
  }

  /**
   * Set page variable
   *
   * we retunt he variable to is can be persisted
   *
   * @param string $name
   * @param string $value
   *
   * @return \Jazzee\Entity\PageVariable
   */
  public function setVar($name, $value)
  {
    foreach ($this->variables as $variable) {
      if ($variable->getName() == $name) {
        $variable->setValue($value);

        return $variable;
      }
    }
    //create a new empty variable with that name
    $variable = new PageVariable;
    $variable->setPage($this);
    $variable->setName($name);
    $this->variables[] = $variable;
    $variable->setValue($value);

    return $variable;
  }

  /**
   * get page variable
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
   * get page variables
   * @return array \Jazzee\Entity\PageVariable
   */
  public function getVariables()
  {
    return $this->variables;
  }

  /**
   * Add element
   *
   * @param Entity\Element $element
   */
  public function addElement(\Jazzee\Entity\Element $element)
  {
    $this->elements[] = $element;
    if ($element->getPage() != $this) {
      $element->setPage($this);
    }
  }

  /**
   * Get elements
   *
   * @return array \Jazzee\Entity\Element
   */
  public function getElements()
  {
    return $this->elements;
  }

  /**
   * Get element by ID
   * @param integer $elementId
   * @return Entity\Element $element
   */
  public function getElementById($elementId)
  {
    foreach ($this->elements as $element) {
      if ($element->getId() == $elementId) {
        return $element;
      }
    }
    foreach ($this->children as $child) {
      if ($element = $child->getElementById($elementId)) {
        return $element;
      }
    }

    return false;
  }

  /**
   * Get element by title
   * @param string $title
   * @return Element $element
   */
  public function getElementByTitle($title)
  {
    foreach ($this->elements as $element) {
      if ($element->getTitle() == $title) {
        return $element;
      }
    }

    return false;
  }

  /**
   * Get element by name
   *
   * @param string $name
   * @return Element $element
   */
  public function getElementByName($name)
  {
    foreach ($this->elements as $element) {
      if ($element->getName() == $name) {
        return $element;
      }
    }

    return false;
  }

  /**
   * Get element by fixed ID
   * @param integer $fixedId
   * @return Entity\Element $element
   */
  public function getElementByFixedId($fixedId)
  {
    foreach ($this->elements as $element) {
      if ($element->getFixedId() == $fixedId) {
        return $element;
      }
    }

    return false;
  }

  /**
   * Create a temporary application page and return a created Jazzee page
   * @return \Jazzee\Interfaces\Page
   */
  public function getApplicationPageJazzeePage()
  {
    if ($this->_applicationPageJazzeePage == null) {
      $this->_applicationPageJazzeePage = $this->getFakeApplicationPage()->getJazzeePage();
    }

    return $this->_applicationPageJazzeePage;
  }

  /**
   * Create a temporary application page
   * @return \Jazzee\Entity\ApplicationPage
   */
  public function getFakeApplicationPage()
  {
    if ($this->_fakeApplicationPage == null) {
      $this->_fakeApplicationPage = new ApplicationPage;
      $this->_fakeApplicationPage->setPage($this);
    }

    return $this->_fakeApplicationPage;
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
   * Get application pages
   *
   * @return array \Jazzee\Entity\ApplicationPage
   */
  public function getApplicationPages()
  {
    return $this->applicationPages;
  }

}