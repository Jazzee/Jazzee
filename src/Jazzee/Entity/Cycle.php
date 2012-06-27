<?php
namespace Jazzee\Entity;

/**
 * Cycle
 * Applications are divided into cycles which represent a single admission period
 *
 * @Entity(repositoryClass="\Jazzee\Entity\CycleRepository")
 * @Table(name="cycles",
 * uniqueConstraints={@UniqueConstraint(name="cycle_name_unique",columns={"name"})})
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Cycle
{

  /**
   * @Id
   * @Column(type="integer")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /** @Column(type="string", length=32) */
  private $name;

  /** @Column(type="datetime", nullable=true) */
  private $start;

  /** @Column(type="datetime", nullable=true) */
  private $end;

  /**
   * @OneToMany(targetEntity="Application", mappedBy="cycle")
   */
  protected $applications;

  /**
   * @ManyToMany(targetEntity="Page")
   * */
  private $requiredPages;

  public function __construct()
  {
    $this->requiredPages = new \Doctrine\Common\Collections\ArrayCollection();
    $this->applications = new \Doctrine\Common\Collections\ArrayCollection();
  }

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
   * Get the start date
   * @return DateTime
   */
  public function getStart()
  {
    return $this->start;
  }

  /**
   * Get the end date
   * @return DateTime
   */
  public function getEnd()
  {
    return $this->end;
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
   * Set the start date
   * @param string $dateTime
   */
  public function setStart($dateTime)
  {
    $start = new \DateTime($dateTime);
    if ($this->end and $start > $this->end) {
      throw new \Jazzee\Exception('Cycle start date must be before end date.');
    }
    $this->start = $start;
  }

  /**
   * Set the end date
   * @param string $dateTime
   */
  public function setEnd($dateTime)
  {
    $end = new \DateTime($dateTime);
    if ($this->start and $end < $this->start) {
      throw new \Jazzee\Exception('Cycle end date must be after start date.');
    }
    $this->end = $end;
  }

  public function clearDates(){
    $this->start = null;
    $this->end = null;
  }

  /**
   * Add a required page
   * @param Page $page
   */
  public function addRequiredPage(Page $page)
  {
    if (!$page->isGlobal()) {
      throw new \Jazzee\Exception("{$page->getTitle()} (#{$page->getId()}) is not a global page and cannot be a required page for a cycle");
    }
    $this->requiredPages[] = $page;
  }

  /**
   * Get the required pages for a cycle
   * @return array Page
   */
  public function getRequiredPages()
  {
    return $this->requiredPages;
  }

  public function hasRequiredPage(Page $page)
  {
    if (count($this->requiredPages) == 0) {
      return false;
    }
    foreach ($this->requiredPages as $p) {
      if ($p == $page) {
        return true;
      }
    }

    return false;
  }

}