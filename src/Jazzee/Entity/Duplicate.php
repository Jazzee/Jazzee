<?php
namespace Jazzee\Entity;

/**
 * Duplicate
 * Identify Duplicate applicants
 *
 * @Entity
 * @Table(name="duplicates",
 *   uniqueConstraints={
 *     @UniqueConstraint(name="duplicate_applicant", columns={"applicant_id", "duplicate_id"})
 *   }
 * )
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Duplicate
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @Column(type="boolean")
   */
  private $isIgnored = false;

  /**
   * @ManyToOne(targetEntity="Applicant",inversedBy="duplicates")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $applicant;

  /**
   * @ManyToOne(targetEntity="Applicant")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $duplicate;

  /**
   * Get the ID
   *
   * @return integer
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set applicant
   *
   * @param Applicant $applicant
   */
  public function setApplicant($applicant)
  {
    $this->applicant = $applicant;
  }

  /**
   * Get applicant
   *
   * @return Applicant $applicant
   */
  public function getApplicant()
  {
    return $this->applicant;
  }

  /**
   * Set duplicate
   *
   * @param Applicant $applicant
   */
  public function setDuplicate($applicant)
  {
    $this->duplicate = $applicant;
  }

  /**
   * Get duplicate
   *
   * @return Applicant $applicant
   */
  public function getDuplicate()
  {
    return $this->duplicate;
  }

  /**
   * Ignore this duplicate
   *
   */
  public function ignore()
  {
    $this->isIgnored = true;
  }

  /**
   * UnIgnore this duplicate
   *
   */
  public function unIgnore()
  {
    $this->isIgnored = false;
  }

  /**
   * Is this duplicate ignored
   * @return boolean
   */
  public function isIgnored()
  {
    return $this->isIgnored;
  }

}