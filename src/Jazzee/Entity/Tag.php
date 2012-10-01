<?php
namespace Jazzee\Entity;

/**
 * Tag
 * Applicants can be tagged with a string to group them - we store all these strings in the tags table and then link them to applicants when they are requested
 *
 * @Entity(repositoryClass="\Jazzee\Entity\TagRepository")
 * @Table(name="tags",
 * uniqueConstraints={@UniqueConstraint(name="tag_title",columns={"title"})})
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Tag
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /** @Column(type="string") */
  private $title;

  /**
   * @ManyToMany(targetEntity="Applicant", mappedBy="tags")
   * */
  private $applicants;

  public function __construct()
  {
    $this->applicants = new \Doctrine\Common\Collections\ArrayCollection();
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
   * Get Applicants
   * array Applicant
   */
  public function getApplicants()
  {
    return $this->applicants;
  }

  /**
   * Set the Applicant
   * @par
   */

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

}