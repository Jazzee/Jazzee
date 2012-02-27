<?php
namespace Jazzee\Entity;

/** 
 * Cycle
 * Applications are divided into cycles which represent a single admission period
 * @Entity(repositoryClass="\Jazzee\Entity\CycleRepository")
 * @Table(name="cycles", 
 * uniqueConstraints={@UniqueConstraint(name="cycle_name_unique",columns={"name"})})
 * @package    jazzee
 * @subpackage orm
 */
class Cycle{
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
   * Get the id
   * @return integer
   */
  public function getId(){
    return $this->id;
  }
  
  /**
   * Get the name
   * @return string
   */
  public function getName(){
    return $this->name;
  }

  /**
   * Get the start date
   * @return DateTime
   */
  public function getStart(){
    return $this->start;
  }
  
  /**
   * Get the end date
   * @return DateTime
   */
  public function getEnd(){
    return $this->end;
  }
  
  /**
   * Set the name
   * @param string $name
   */
  public function setName($name){
    $this->name = $name;
  }

  /**
   * Set the start date
   * @param string $dateTime
   */
  public function setStart($dateTime){
    $start = new \DateTime($dateTime);
    if($this->end and $start > $this->end) throw new \Jazzee\Exception('Cycle start date must be before end date.');
    $this->start = $start;
  }
  
  /**
   * Set the end date
   * @param string $dateTime
   */
  public function setEnd($dateTime){
    $end = new \DateTime($dateTime);
    if($this->start and $end < $this->start) throw new \Jazzee\Exception('Cycle end date must be after start date.');
    $this->end = $end;
  }
}

/**
 * CycleRepository
 * Special Repository methods for Cycles
 * @package jazzee
 * @subpackage orm
 */
class CycleRepository extends \Doctrine\ORM\EntityRepository{
  
  /**
   * find best current cycle
   * 
   * If a user doesn't have a cycle we need to search for an find the best current cycle for them
   * @param Program $program
   * @return Cycle
   */
  public function findBestCycle(Program $program){
    $query = $this->_em->createQuery('SELECT c FROM Jazzee\Entity\Cycle c JOIN c.applications a WHERE a.program = :program ORDER BY c.end DESC');
    $query->setParameter('program', $program);
    $result = $query->getResult();
    if(count($result)) return $result[0];
    return false;
  }
}