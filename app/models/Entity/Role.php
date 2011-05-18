<?php
namespace Entity;

/** 
 * Role
 * Roles grant access to admin users
 * @Entity @Table(name="roles") 
 * @package    jazzee
 * @subpackage orm
 **/
class Role{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** @Column(type="string") */
  private $name;
  
  /** @Column(type="boolean") */
  private $isGlobal;
  
  /** 
   * @OneToMany(targetEntity="RoleAction", mappedBy="role")
   */
  private $actions;
  
  /**
   * @ManyToMany(targetEntity="User", mappedBy="roles")
  **/
  private $users;

  public function __construct(){
    $this->actions = new \Doctrine\Common\Collections\ArrayCollection();
    $this->users = new \Doctrine\Common\Collections\ArrayCollection();
  }
  
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

  /**
   * Make global
   */
  public function makeGlobal(){
    $this->isGlobal = true;
  }
  
/**
   * Make not global
   */
  public function notGlobal(){
    $this->isGlobal = false;
  }

  /**
   * Get global status
   * @return boolean $isGlobal
   */
  public function isGlobal(){
    return $this->isGlobal;
  }

  /**
   * Add actions
   *
   * @param Entity\RoleAction $action
   */
  public function addAction(RoleAction $action){
    $this->actions[] = $action;
  }

  /**
   * Get actions
   *
   * @return Doctrine\Common\Collections\Collection $actions
   */
  public function getActions(){
    return $this->actions;
  }

  /**
   * Add user
   *
   * @param Entity\User $user
   */
  public function addUser(\Entity\User $user){
    $this->users[] = $user;
  }

  /**
   * Get users
   *
   * @return Doctrine\Common\Collections\Collection $users
   */
  public function getUsers(){
    return $this->users;
  }
  
}