<?php
namespace Jazzee\Entity;

/** 
 * RoleAction
 * Actions allowed by this Role
 * @Entity @Table(name="role_actions") 
 * @package    jazzee
 * @subpackage orm
 **/ 
class RoleAction{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** 
   * @ManyToOne(targetEntity="Role", inversedBy="actions")
   * @JoinColumn(onDelete="CASCADE",onUpdate="CASCADE") 
   */
  private $role;
  
  /** @Column(type="string") */
  private $controller;
  
  /** @Column(type="string") */
  private $action;
  

  /**
   * Get id
   *
   * @return bigint $id
   */
  public function getId(){
    return $this->id;
  }

  /**
   * Set controller
   *
   * @param string $controller
   */
  public function setController($controller){
    $this->controller = $controller;
  }

  /**
   * Get controller
   *
   * @return string $controller
   */
  public function getController(){
    return $this->controller;
  }

  /**
   * Set action
   *
   * @param string $action
   */
  public function setAction($action){
    $this->action = $action;
  }

  /**
   * Get action
   *
   * @return string $action
   */
  public function getAction(){
    return $this->action;
  }
  
  /**
   * set role
   *
   * @param \Jazzee\Entity\Role $role
   */
  public function setRole(\Jazzee\Entity\Role $role){
    $this->role = $role;
  }
}