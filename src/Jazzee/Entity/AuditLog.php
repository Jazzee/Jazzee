<?php
namespace Jazzee\Entity;

/**
 * AuditLog
 * AuditLog entries record critical user actions against applicants
 * Like editing or deleting answers
 *
 * @Entity
 * @Table(name="audit_log")
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class AuditLog
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /** @Column(type="datetime") */
  protected $createdAt;

  /** @Column(type="text") */
  private $text;

  /**
   * @ManyToOne(targetEntity="User",inversedBy="auditLogs")
   * @JoinColumn(onDelete="CASCADE")
   */
  protected $user;

  /**
   * @ManyToOne(targetEntity="Applicant",inversedBy="auditLogs")
   * @JoinColumn(onDelete="CASCADE")
   */
  protected $applicant;

  /**
   * Constructor
   * Everythign is specified here and can't be set any other way
   * @param User $user
   * @param Applicant $applicant
   * @param strig $text
   */
  public function __construct(User $user, Applicant $applicant, $text)
  {
    $this->createdAt = new \DateTime('now');
    $this->user = $user;
    $this->applicant = $applicant;
    $this->text = $text;
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
   * Get createdAt
   *
   * @return \DateTime $createdAt
   */
  public function getCreatedAt()
  {
    return $this->createdAt;
  }

  /**
   * Get user
   *
   * @return Entity\User $user
   */
  public function getUser()
  {
    return $this->user;
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
   * Get text
   *
   * @return string $text
   */
  public function getText()
  {
    return $this->text;
  }

}