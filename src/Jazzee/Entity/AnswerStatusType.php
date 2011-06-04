<?php
namespace Jazzee\Entity;

/** 
 * AnswerStatusType
 * types of answer status
 * @Entity 
 * @Table(name="answer_status_types", 
 * uniqueConstraints={@UniqueConstraint(name="answerstatustype_name",columns={"name"})})
 * @package    jazzee
 * @subpackage orm
 **/
class AnswerStatusType{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** @Column(type="string") */
  private $name;
  
  /**
   * Get id
   *
   * @return bigint $id
   */
  public function getId(){
    return $this->id;
  }

  /**
   * Set name
   *
   * @param string $name
   */
  public function setName($name){
    $this->name = $name;
  }

  /**
   * Get name
   *
   * @return string $name
   */
  public function getName(){
    return $this->name;
  }
}