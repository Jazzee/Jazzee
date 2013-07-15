<?php
namespace Jazzee\Entity;

/**
 * ApplicationPage
 * Assocaites a Page with an Application.  Allows the application to override many of the page varialbes for global pages
 *
 * @HasLifecycleCallbacks
 * @Entity
 * @Table(name="application_pages",uniqueConstraints={
 *   @UniqueConstraint(name="application_page", columns={"application_id", "page_id"}),
 *   @UniqueConstraint(name="applicationpage_name", columns={"application_id", "name"})
 * })
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplicationPage
{
  /**
   * Page type for regular application apges
   */

  const APPLICATION = 2;

  /**
   * Page type for SIR accept
   */
  const SIR_ACCEPT = 4;

  /**
   * Page type for SIR decline
   */
  const SIR_DECLINE = 8;

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @ManyToOne(targetEntity="Application", inversedBy="applicationPages")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $application;

  /**
   * @ManyToOne(targetEntity="Page", inversedBy="applicationPages")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $page;

  /** @Column(type="integer") */
  private $weight;

  /** @Column(type="integer") */
  private $kind;

  /** @Column(type="string", nullable=true) */
  private $title;

  /** @Column(type="string", nullable=true) */
  private $name;

  /** @Column(type="integer", nullable=true) */
  private $min;

  /** @Column(type="integer", nullable=true) */
  private $max;

  /** @Column(type="boolean", nullable=true) */
  private $isRequired;

  /** @Column(type="boolean", nullable=true) */
  private $answerStatusDisplay;

  /** @Column(type="text", nullable=true) */
  private $instructions;

  /** @Column(type="text", nullable=true) */
  private $leadingText;

  /** @Column(type="text", nullable=true) */
  private $trailingText;

  /**
   * The Jazzee Page instance
   * @var \Jazzee\Interfaces\Page
   */
  private $jazzeePage;

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
   * Set application
   *
   * @param Application $application
   */
  public function setApplication(Application $application)
  {
    $this->application = $application;
  }

  /**
   * get application
   *
   * @return Application $application
   */
  public function getApplication()
  {
    return $this->application;
  }

  /**
   * Set page
   *
   * @param Page $page
   */
  public function setPage(Page $page)
  {
    $this->page = $page;
  }

  /**
   * Get page
   *
   * @return Page
   */
  public function getPage()
  {
    return $this->page;
  }

  /**
   * Get the kind
   */
  public function getKind()
  {
    return $this->kind;
  }

  /**
   * Set the kind
   * @param integer $kind
   */
  public function setKind($kind)
  {
    $allowed = array(self::APPLICATION, self::SIR_ACCEPT, self::SIR_DECLINE);
    if (!in_array($kind, $allowed)) {
      throw new \Jazzee\Exception($kind . ' is not a valid application page kind.');
    }
    if (($kind == self::SIR_ACCEPT or $kind == self::SIR_DECLINE) AND !($this->getJazzeePage() instanceof \Jazzee\Interfaces\SirPage)) {
      throw new \Jazzee\Exception('Tried to set an SIR page kind, but ' . $this->getPage()->getType()->getClass() . ' does not implement \Jazzee\Interfaces\SirPage');
    }
    $this->kind = $kind;
  }

  /**
   * Check constraints
   * Ensure there is no more than one application page of kind SIR_ACCPT or SIR_DECLINE
   * @PrePersist
   */
  public function checkConstraints()
  {
    $kinds = array(self::SIR_ACCEPT => 'SIR_ACCEPT', self::SIR_DECLINE => 'SIR_DECLINE');
    foreach ($kinds as $kind => $name) {
      if ($this->kind == $kind) {
        foreach ($this->application->getApplicationPages($kind) as $page) {
          if ($page !== $this) {
            throw new \Jazzee\Exception("{$this->getTitle()} and {$page->getTitle()} both have the kind {$name}.  This is not allowed.");
          }
        }

        return true;
      }
    }

    return true;
  }

  /**
   * Get the weight
   */
  public function getWeight()
  {
    return $this->weight;
  }

  /**
   * Set the weight
   * @param integer $value
   */
  public function setWeight($weight)
  {
    $this->weight = $weight;
  }

  /**
   * Get the title
   * If the title is not overridde then use the one from Page
   */
  public function getTitle()
  {
    if (is_null($this->title)) {
      return $this->page->getTitle();
    }

    return $this->title;
  }

  /**
   * Set the title
   * If this isn't a global page then store the title in Page and not here
   * @param string $title
   */
  public function setTitle($value)
  {
    if (!$this->page->isGlobal()) {
      $this->page->setTitle($value);
    } else {
      $this->title = $value;
    }
  }

  /**
   * Get the uniqueName
   *
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set the name
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
   * Get the min
   */
  public function getMin()
  {
    if (is_null($this->min)) {
      return $this->page->getMin();
    }

    return $this->min;
  }

  /**
   * Set the min
   * If this isn't a global page then store the min in Page and not here
   * @param string $value
   */
  public function setMin($value)
  {
    if (empty($value)) {
      $value = null;
    }
    if (!$this->page->isGlobal()) {
      $this->page->setMin($value);
    } else {
      $this->min = $value;
    }
  }

  /**
   * Get the max
   */
  public function getMax()
  {
    if (is_null($this->max)) {
      return $this->page->getMax();
    }

    return $this->max;
  }

  /**
   * Set the max
   * If this isn't a global page then store the max in Page and not here
   * @param string $value
   */
  public function setMax($value)
  {
    if (empty($value)) {
      $value = null;
    }
    if (!$this->page->isGlobal()) {
      $this->page->setMax($value);
    } else {
      $this->max = $value;
    }
  }

  /**
   * Is this page required
   */
  public function isRequired()
  {
    if (is_null($this->isRequired)) {
      return $this->page->isRequired();
    }

    return $this->isRequired;
  }

  /**
   * Make this page as required
   * If this isn't a global page then store the required in Page and not here
   */
  public function required()
  {
    if (!$this->page->isGlobal()) {
      $this->page->required();
    } else {
      $this->isRequired = true;
    }
  }

  /**
   * Make this page optional
   * If this isn't a global page then store the required in Page and not here
   */
  public function optional()
  {
    if (!$this->page->isGlobal()) {
      $this->page->optional();
    } else {
      $this->isRequired = false;
    }
  }

  /**
   * Show the answer status
   */
  public function showAnswerStatus()
  {
    if (!$this->page->isGlobal()) {
      $this->page->showAnswerStatus();
    } else {
      $this->answerStatusDisplay = true;
    }
  }

  /**
   * Hide the answer status
   */
  public function hideAnswerStatus()
  {
    if (!$this->page->isGlobal()) {
      $this->page->hideAnswerStatus();
    } else {
      $this->answerStatusDisplay = false;
    }
  }

  /**
   * Display answer status value
   */
  public function answerStatusDisplay()
  {
    if (is_null($this->answerStatusDisplay)) {
      return $this->page->answerStatusDisplay();
    }

    return $this->answerStatusDisplay;
  }

  /**
   * Get the instructions
   */
  public function getInstructions()
  {
    if (is_null($this->instructions)) {
      return $this->page->getInstructions();
    }

    return $this->instructions;
  }

  /**
   * Set the instructions
   * If this isn't a global page then store the instructions in Page and not here
   * @param string $value
   */
  public function setInstructions($value)
  {
    if (empty($value)) {
      $value = null;
    }
    if (!$this->page->isGlobal()) {
      $this->page->setInstructions($value);
    } else {
      $this->instructions = $value;
    }
  }

  /**
   * Get the leadingText
   */
  public function getLeadingText()
  {
    if (is_null($this->leadingText)) {
      return $this->page->getLeadingText();
    }

    return $this->leadingText;
  }

  /**
   * Set the leadingText
   * If this isn't a global page then store the title in Page and not here
   * @param string $value
   */
  public function setLeadingText($value)
  {
    if (empty($value)) {
      $value = null;
    }
    if (!$this->page->isGlobal()) {
      $this->page->setLeadingText($value);
    } else {
      $this->leadingText = $value;
    }
  }

  /**
   * Get the trailingText
   */
  public function getTrailingText()
  {
    if (is_null($this->trailingText)) {
      return $this->getPage()->getTrailingText();
    }

    return $this->trailingText;
  }

  /**
   * Set the trailingText
   * If this isn't a global page then store the title in Page and not here
   * @param string $value
   */
  public function setTrailingText($value)
  {
    if (empty($value)) {
      $value = null;
    }
    if (!$this->page->isGlobal()) {
      $this->page->setTrailingText($value);
    } else {
      $this->trailingText = $value;
    }
  }

  /**
   * Get the jazzeePage
   *
   * @return \Jazzee\Interfaces\Page
   */
  public function getJazzeePage()
  {
    if (is_null($this->jazzeePage)) {
      $className = $this->page->getType()->getClass();
      $class = new $className($this);
      if (!($class instanceof \Jazzee\Interfaces\Page)) {
        throw new \Jazzee\Exception($this->page->getType()->getName() . ' has class ' . $class . ' that does not implement \Jazzee\Interfaces\Page interface');
      }
      $this->jazzeePage = $class;
    }

    return $this->jazzeePage;
  }

}