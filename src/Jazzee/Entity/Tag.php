<?php
namespace Jazzee\Entity;

/** 
 * Tag
 * Applicants can be tagged with a string to group them - we store all these strings in the tags table and then link them to applicants when they are requested
 * @Entity @Table(name="tags", 
 * uniqueConstraints={@UniqueConstraint(name="tag_title",columns={"title"})})
 * @package    jazzee
 * @subpackage orm
 **/
class Tag{
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
  **/
  private $applicants;
  

  /**
   * Get id
   *
   * @return bigint $id
   */
  public function getId(){
    return $this->id;
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
  public function setTitle($title){
    $this->title = $title;
  }

  /**
   * Get title
   *
   * @return string $title
   */
  public function getTitle(){
    return $this->title;
  }
}