<?php
namespace Jazzee\Entity;

/** 
 * Role
 * Roles grant access to admin users
 * @Entity 
 * @Table(name="roles") 
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
   * @ManyToOne(targetEntity="Program")
   */
  private $program;
  
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
    if($this->program) throw new \Jazzee\Exception("{this->name} is set for program " . $program->getName() . ' but you are trying to make it global.');
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
   * Set program
   *
   * @param \Jazzee\Entity\Program $program
   */
  public function setProgram(Program $program){
    if($this->isGlobal) throw new \Jazzee\Exception("{this->name} is global but you are trying to set its program to " . $program->getName());
    $this->program = $program;
  }

  /**
   * Get program
   *
   * @return \Jazzee\Entity\Program $program
   */
  public function getProgram(){
    return $this->program;
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
  
  /**
   * Check if an action is allowed by this role
   * 
   * @param string $controller
   * @param string $action
   * @param \Jazzee\Entity\Program $program
   * @return bool
   */
  public function isAllowed($controllerName, $actionName){
    foreach($this->actions as $action){
      if($action->getController() == $controllerName and $action->getAction() == strtolower($actionName)) return true;
    }
    return false;
  }
  
}