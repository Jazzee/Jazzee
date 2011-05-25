<?php
namespace Entity;

/** 
 * User
 * Admin users details
 * @Entity @Table(name="users") 
 * @package    jazzee
 * @subpackage orm
 **/
class User{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /**
   * Unique campus ID for use with campus AuthN/AuthZ 
   * @Column(type="string", unique=true) 
   * */
  private $campusId;
  
  /** @Column(type="string", unique=true) */
  private $email;
  
  /** @Column(type="string") */
  private $password;
  
  /** @Column(type="string") */
  private $activateToken;
  
  /** @Column(type="string", unique=true) */
  private $apiKey;
  
  /** @Column(type="string") */
  private $firstName;
  
  /** @Column(type="string") */
  private $lastName;
  
  /** @Column(type="datetime", nullable=true) */
  private $lastLogin;
  
  /** @Column(type="string", length="15", nullable=true) */
  private $lastLoginIp;
  
  /** @Column(type="string", length="15", nullable=true) */
  private $lastFailedLoginIp;
  
  /** @Column(type="integer") */
  private $failedLoginAttempts;
  
  /** @Column(type="boolean") */
  private $expired;
  
  /** 
   * @ManyToOne(targetEntity="Program",cascade={"all"})
   * @JoinColumn(onUpdate="CASCADE") 
   */
  private $defaultProgram;
  
  /** 
   * @ManyToOne(targetEntity="Cycle",cascade={"all"})
   * @JoinColumn(onUpdate="CASCADE") 
   */
  private $defaultCycle;
  
  /**
   * @ManyToMany(targetEntity="Role", inversedBy="users")
   * @JoinTable(name="user_roles")
  **/
  private $roles;
  
  /** 
   * @OneToMany(targetEntity="Message",mappedBy="user") 
   */
  private $messages;
  

  public function __construct(){
    $this->roles = new \Doctrine\Common\Collections\ArrayCollection();
    $this->messages = new \Doctrine\Common\Collections\ArrayCollection();
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
   * Set campusId
   *
   * @param string $campusId
   */
  public function setCampusId($campusId){
    $this->campusId = $campusId;
  }

  /**
   * Get campusId
   *
   * @return string $campusId
   */
  public function getCampusId(){
    return $this->campusId;
  }

  /**
   * Set email
   *
   * @param string $email
   */
  public function setEmail($email){
    $this->email = $email;
  }

  /**
   * Get email
   *
   * @return string $email
   */
  public function getEmail(){
    return $this->email;
  }

  
  /**
   * Set password
   *
   * @param string $password
   */
  public function setPassword($password){
    $p = new PasswordHash(8, FALSE);
    $this->password = $p->HashPassword($password);
  }
  
  /**
   * Set Hashed password
   * Store the previously hashed version of the password
   * @param string $password
   */
  public function setHashedPassword($password){
    $this->password = $password;
  }
    
  /**
   * Check a password against its hash
   * @param string $password
   * @param string $hashedPassword
   */
  public function checkPassword($password){
    $p = new PasswordHash(8, FALSE);
    return $p->CheckPassword($password, $this->password);
  }

  /**
   * Get password
   *
   * @return string $password
   */
  public function getPassword(){
    return $this->password;
  }

  /**
   * Set activeToken
   *
   * @param string $activeToken
   */
  public function setActivateToken($activeToken){
    $this->activateToken = $activeToken;
  }

  /**
   * Get activeToken
   *
   * @return string $activeToken
   */
  public function getActivateTokenToken(){
    return $this->activateToken;
  }

  /**
   * Set apiKey
   *
   * @param string $apiKey
   */
  public function setApiKey($apiKey){
    $this->apiKey = $apiKey;
  }

  /**
   * Get apiKey
   *
   * @return string $apiKey
   */
  public function getApiKey(){
    return $this->apiKey;
  }

  /**
   * Set firstName
   *
   * @param string $firstName
   */
  public function setFirstName($firstName){
    $this->firstName = $firstName;
  }

  /**
   * Get firstName
   *
   * @return string $firstName
   */
  public function getFirstName(){
    return $this->firstName;
  }

  /**
   * Set lastName
   *
   * @param string $lastName
   */
  public function setLastName($lastName){
    $this->lastName = $lastName;
  }

  /**
   * Get lastName
   *
   * @return string $lastName
   */
  public function getLastName(){
    return $this->lastName;
  }

  /**
   * Set lastLogin
   *
   * @param strint $lastLogin
   */
  public function setLastLogin($lastLogin){
    $this->lastLogin = new \DateTime($lastLogin);
  }

  /**
   * Get lastLogin
   *
   * @return DateTime $lastLogin
   */
  public function getLastLogin(){
    return $this->lastLogin;
  }

  /**
   * Set lastLoginIp
   *
   * @param string $lastLoginIp
   */
  public function setLastLoginIp($lastLoginIp){
    $this->lastLoginIp = $lastLoginIp;
  }

  /**
   * Get lastLoginIp
   *
   * @return string $lastLoginIp
   */
  public function getLastLoginIp(){
    return $this->lastLoginIp;
  }

  /**
   * Set lastFailedLoginIp
   *
   * @param string $lastFailedLoginIp
   */
  public function setLastFailedLoginIp($lastFailedLoginIp){
    $this->lastFailedLoginIp = $lastFailedLoginIp;
  }

  /**
   * Get lastFailedLoginIp
   *
   * @return string $lastFailedLoginIp
   */
  public function getLastFailedLoginIp(){
    return $this->lastFailedLoginIp;
  }

  /**
   * Set failedLoginAttempts
   *
   * @param integer $failedLoginAttempts
   */
  public function setFailedLoginAttempts($failedLoginAttempts){
    $this->failedLoginAttempts = $failedLoginAttempts;
  }

  /**
   * Get failedLoginAttempts
   *
   * @return integer $failedLoginAttempts
   */
  public function getFailedLoginAttempts(){
    return $this->failedLoginAttempts;
  }

  /**
   * Expire User
   */
  public function expire(){
    $this->expired = true;
  }
  
  /**
   * UnExpire User
   */
  public function unExpire(){
    $this->expired = false;
  }

  /**
   * Get expired status
   *
   * @return datetime $expires
   */
  public function isExpired(){
    return $this->expired;
  }

  /**
   * Set defaultProgram
   *
   * @param Entity\Program $defaultProgram
   */
  public function setDefaultProgram(Program $defaultProgram){
    $this->defaultProgram = $defaultProgram;
  }

  /**
   * Get defaultProgram
   *
   * @return Entity\Program $defaultProgram
   */
  public function getDefaultProgram(){
    return $this->defaultProgram;
  }

  /**
   * Set defaultCycle
   *
   * @param Entity\Cycle $defaultCycle
   */
  public function setDefaultCycle(Cycle $defaultCycle){
    $this->defaultCycle = $defaultCycle;
  }

  /**
   * Get defaultCycle
   *
   * @return Entity\Cycle $defaultCycle
   */
  public function getDefaultCycle(){
    return $this->defaultCycle;
  }

  /**
   * Add role
   *
   * @param Entity\Role $role
   */
  public function addRole(Role $role){
    $this->roles[] = $role;
  }

  /**
   * Get roles
   *
   * @return Doctrine\Common\Collections\Collection $roles
   */
  public function getRoles(){
    return $this->roles;
  }

  /**
   * Get messages
   *
   * @return Doctrine\Common\Collections\Collection $messages
   */
  public function getMessages(){
    return $this->messages;
  }
}