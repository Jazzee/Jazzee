<?php
namespace Entity;

/** 
 * AnswerStatusType
 * types of answer status
 * @Entity @Table(name="answer_status_types") 
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