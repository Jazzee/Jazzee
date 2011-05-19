<?php
namespace Entity;

/** 
 * Program
 * Represents a single program which contains Applications for Cycles 
 * and Users with roles in the program
 * @Entity @Table(name="programs") 
 * @package    jazzee
 * @subpackage orm
 **/
class Program{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** @Column(type="string", unique=true) */
  private $name;
  
  /** @Column(type="string", length=32, unique=true) */
  private $shortName;
  
  /** @Column(type="boolean") */
  private $isExpired = false;
  
  
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
   * Get the shortName
   * @return string
   */
  public function getShortName(){
    return $this->shortName;
  }

  /**
   * Get the start date
   * @return DateTime
   */
  public function getExpires(){
    return $this->expires;
  }
  
  /**
   * Set the name
   * @param string $name
   */
  public function setName($name){
    $this->name = $name;
  }
  
  /**
   * Set the shortName
   * @param string $shortName
   */
  public function setShortName($shortName){
    $this->shortName = $shortName;
  }

  /**
   * Expire the program
   */
  public function expire(){
    $this->isExpired = true;
  }
  
  /**
   * UnExpire the program
   */
  public function unExpire(){
    $this->isExpired = false;
  }
  
  /**
   * Get expires status
   */
  public function isExpires(){
    return $this->isEpired;
  }
}