<?php
namespace Jazzee\Entity;

/**
 * User
 * Admin users details
 *
 * @Entity(repositoryClass="\Jazzee\Entity\UserRepository")
 * @Table(name="users",
 * uniqueConstraints={@UniqueConstraint(name="user_name",columns={"uniqueName"})})
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class User
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * Unique name for use with federated authentication
   * @Column(type="string")
   * */
  private $uniqueName;

  /** @Column(type="string", nullable=true) */
  private $email;

  /** @Column(type="string", nullable=true) */
  private $firstName;

  /** @Column(type="string", nullable=true) */
  private $lastName;

  /** @Column(type="string", nullable=true) */
  private $apiKey;

  /** @Column(type="boolean") */
  private $isActive;
  
  /** @Column(type="array", nullable=true) */
  private $preferences;

  /**
   * @ManyToOne(targetEntity="Program")
   * @JoinColumn(onDelete="SET NULL")
   */
  private $defaultProgram;

  /**
   * @ManyToOne(targetEntity="Cycle")
   * @JoinColumn(onDelete="SET NULL")
   */
  private $defaultCycle;

  /**
   * @ManyToMany(targetEntity="Role", inversedBy="users")
   * @JoinTable(name="user_roles")
   * */
  private $roles;

  /**
   * @OneToMany(targetEntity="AuditLog", mappedBy="user")
   */
  protected $auditLogs;

  /**
   * @OneToMany(targetEntity="Display", mappedBy="user")
   */
  protected $displays;

  public function __construct()
  {
    $this->isActive = true;
    $this->roles = new \Doctrine\Common\Collections\ArrayCollection();
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
   * Set uniqueName
   *
   * @param string $uniqueName
   */
  public function setUniqueName($uniqueName)
  {
    $this->uniqueName = $uniqueName;
  }

  /**
   * Get uniqueName
   *
   * @return string $uniqueName
   */
  public function getUniqueName()
  {
    return $this->uniqueName;
  }

  /**
   * Set email
   *
   * @param string $email
   */
  public function setEmail($email)
  {
    $this->email = $email;
  }

  /**
   * Get email
   *
   * @return string $email
   */
  public function getEmail()
  {
    return $this->email;
  }

  /**
   * Set firstName
   *
   * @param string $firstName
   */
  public function setFirstName($firstName)
  {
    $this->firstName = $firstName;
  }

  /**
   * Get firstName
   *
   * @return string $firstName
   */
  public function getFirstName()
  {
    return $this->firstName;
  }

  /**
   * Set lastName
   *
   * @param string $lastName
   */
  public function setLastName($lastName)
  {
    $this->lastName = $lastName;
  }

  /**
   * Get lastName
   *
   * @return string $lastName
   */
  public function getLastName()
  {
    return $this->lastName;
  }

  /**
   * Generate apiKey
   */
  public function generateApiKey()
  {
    //PHPs uniquid function is time based and therefor guessable
    //So we get unique through uniquid and we get random by prefixing it with a part of an MD5
    $this->apiKey = \uniqid(md5((mt_rand() * mt_rand() * $this->id) . $this->uniqueName . $this->lastName . $this->firstName));
  }

  /**
   * get apiKey
   */
  public function getApiKey()
  {
    return $this->apiKey;
  }

  /**
   * set apiKey
   * @param string $apiKey
   */
  public function setApiKey($apiKey)
  {
    if(strlen($apiKey) < 32){
      throw new \Jazzee\Exception("Api key must be at least 32 charecters you passed: {$apiKey}");
    }
    $this->apiKey = $apiKey;
  }

  /**
   * Is the user active
   * @return boolean
   */
  public function isActive()
  {
    return $this->isActive;
  }

  /**
   * Active User
   *
   */
  public function activate()
  {
    $this->isActive = true;
  }

  /**
   * Deactivate User
   */
  public function deActivate()
  {
    $this->isActive = false;
    $this->roles->clear();
  }

  /**
   * Set defaultProgram
   *
   * @param Entity\Program $defaultProgram
   */
  public function setDefaultProgram(Program $defaultProgram)
  {
    $this->defaultProgram = $defaultProgram;
  }

  /**
   * Get defaultProgram
   *
   * @return Entity\Program $defaultProgram
   */
  public function getDefaultProgram()
  {
    return $this->defaultProgram;
  }

  /**
   * Set defaultCycle
   *
   * @param Entity\Cycle $defaultCycle
   */
  public function setDefaultCycle(Cycle $defaultCycle)
  {
    $this->defaultCycle = $defaultCycle;
  }

  /**
   * Get defaultCycle
   *
   * @return Entity\Cycle $defaultCycle
   */
  public function getDefaultCycle()
  {
    return $this->defaultCycle;
  }

  /**
   * Add role
   *
   * @param Entity\Role $role
   */
  public function addRole(Role $role)
  {
    $this->roles[] = $role;
  }

  /**
   * Get roles
   *
   * @return Doctrine\Common\Collections\Collection $roles
   */
  public function getRoles()
  {
    return $this->roles;
  }

  /**
   * Has role
   * Check if a user has a role
   * @param \Jazzee\Entity\Role $role
   * @return boolean
   */
  public function hasRole(Role $role)
  {
    foreach ($this->roles as $r) {
      if ($r == $role) {
        return true;
      }
    }

    return false;
  }

  /**
   * Add log
   *
   * @param Entity\Log $log
   */
  public function addLog(AuditLog $log)
  {
    $this->logs[] = $log;
  }

  /**
   * Get logs
   *
   * @return Doctrine\Common\Collections\Collection $logs
   */
  public function getLogs()
  {
    return $this->logs;
  }

  /**
   * get an array of all the users program affiliations
   * @return array
   */
  public function getPrograms()
  {
    $programs = array();
    foreach ($this->roles as $role) {
      if ($role->getProgram()) {
        $programs[] = $role->getProgram()->getId();
      }
    }
    array_unique($programs);

    return $programs;
  }

  /**
   * Add log
   *
   * @param int $applicationId
   * @param \stdClass $preferences
   */
  public function setPreferences($applicationId, \stdClass $preferences)
  {
    $this->preferences[$applicationId] = $preferences;
  }

  /**
   * Get preferences
   * @param int $applicationId
   * 
   * @return \stdClass
   */
  public function getPreferences($applicationId)
  {
    if(!is_null($this->preferences) AND array_key_exists($applicationId, $this->preferences)){
      return $this->preferences[$applicationId];
    }

    return new \stdClass();
  }

  /**
   * Check if a user is allowed to access a resource
   *
   * @param string $controller
   * @param string $action
   * @param \Jazzee\Entity\Program $program
   */
  public function isAllowed($controller, $action, \Jazzee\Entity\Program $program = null)
  {
    foreach ($this->roles as $role) {
      if (($role->isGlobal() or $role->getProgram() == $program) and $role->isAllowed($controller, $action)) {
        return true;
      }
    }

    return false;
  }
  
  
  public function getMaximumDisplayForApplication(\Jazzee\Entity\Application $application)
  {
    $display = new \Jazzee\Display\Union();
    $hasProgramRole = false;
    foreach($this->roles as $role){
      if(!$role->isGlobal() AND $role->getProgram()->getId() == $application->getProgram()->getId()){
        $hasProgramRole = true;
        if($roleDisplay = $role->getDisplay()){
          $display->addDisplay($roleDisplay);
        } else {
          $display->addDisplay(new \Jazzee\Display\FullApplication($application));
          break; //we interupt the loop here because once we have included a full display there is no reason to continue
        }
      }
    }
    //Temporary solution global users dont generally have program roles, but get access to all the elementns
    //for any action they can see
    if(!$hasProgramRole){
      $display->addDisplay(new \Jazzee\Display\FullApplication($application));
    }

    return $display;
  }

}