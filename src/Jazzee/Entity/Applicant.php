<?php
namespace Jazzee\Entity;

/** 
 * Applicant
 * Individual applicants are tied to an Application - but a single person can be multiple Applicants
 * @Entity(repositoryClass="\Jazzee\Entity\ApplicantRepository")
 * @HasLifecycleCallbacks 
 * @Table(name="applicants",
 *   uniqueConstraints={
 *     @UniqueConstraint(name="application_email", columns={"application_id", "email"}),
 *     @UniqueConstraint(name="applicant_uniqueId", columns={"uniqueId"})
 *   },
 *   indexes={@index(name="applicant_email", columns={"email"})}
 * ) 
 * @package    jazzee
 * @subpackage orm
 **/
class Applicant{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** @Column(type="string", length=255, nullable=true) */
  private $uniqueId;
  
  /** 
   * @ManyToOne(targetEntity="Application", inversedBy="applicants")
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE") 
   */
  private $application;
  
  /** @Column(type="string") */
  private $email;
  
  /** @Column(type="string") */
  private $password;
  
  /** @Column(type="boolean") */
  private $isLocked;
  
  /** @Column(type="string") */
  private $firstName;
  
  /** @Column(type="string", nullable=true) */
  private $middleName;
  
  /** @Column(type="string") */
  private $lastName;
  
  /** @Column(type="string", nullable=true) */
  private $suffix;
  
  /** @Column(type="datetime", nullable=true) */
  private $deadlineExtension;
  
  /** @Column(type="datetime", nullable=true) */
  private $lastLogin;
  
  /** @Column(type="string", length="15", nullable=true) */
  private $lastLoginIp;
  
  /** @Column(type="string", length="15", nullable=true) */
  private $lastFailedLoginIp;
  
  /** @Column(type="integer", nullable=true) */
  private $failedLoginAttempts;
  
  /** @Column(type="datetime", nullable=true) */
  private $createdAt;
  
  /** @Column(type="datetime", nullable=true) */
  private $updatedAt;
  
  /** @Column(type="float") */
  private $percentComplete;
  
  /** 
   * @OneToMany(targetEntity="Answer",mappedBy="applicant")
   */
  private $answers;
  
  /** 
   * @OneToMany(targetEntity="Attachment",mappedBy="applicant")
   */
  private $attachments;
  
  /** 
   * @OneToOne(targetEntity="Decision",mappedBy="applicant", cascade={"persist"})
   */
  private $decision;
  
  /**
   * @ManyToMany(targetEntity="Tag", inversedBy="applicants")
   * @JoinTable(name="applicant_tags")
  **/
  private $tags;
  
  /** 
   * @OneToMany(targetEntity="Thread",mappedBy="applicant")
   * @OrderBy({"createdAt" = "ASC"})
   */
  private $threads;
  
  /**
   * @OneToMany(targetEntity="Duplicate", mappedBy="applicant")
   */
  private $duplicates;
  
  /** 
   * @OneToMany(targetEntity="AuditLog", mappedBy="applicant")
   */
  protected $audiLogs;
  
  /**
   * If we set a manual update don't override it
   * @var boolean
   */
  private $updatedAtOveridden = false;
  
  public function __construct(){
    $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
    $this->attachments = new \Doctrine\Common\Collections\ArrayCollection();
    $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
    $this->threads = new \Doctrine\Common\Collections\ArrayCollection();
    $this->duplicates = new \Doctrine\Common\Collections\ArrayCollection();
    $this->createdAt = new \DateTime('now');
    $this->isLocked = false;
    $this->percentComplete = 0;
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
    $p = new \PasswordHash(8, FALSE);
    $this->password = $p->HashPassword($password);
    //when a new password is set reset the failedLogin counter
    $this->failedLoginAttempts = 0;
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
   * Get password
   *
   * @return string $password
   */
  public function getPassword(){
    return $this->password;
  }
    
  /**
   * Check a password against its hash
   * @param string $password
   * @param string $hashedPassword
   */
  public function checkPassword($password){
    $p = new \PasswordHash(8, FALSE);
    return $p->CheckPassword($password, $this->password);
  }

  /**
   * Generate a unique id
   */
  public function generateUniqueId(){
    //PHPs uniquid function is time based and therefor guessable
    //A stright random MD5 sum is too long for email and tends to line break causing usability problems
    //So we get unique through uniquid and we get random by prefixing it with a part of an MD5
    //hopefully this results in a URL friendly short, but unguessable string
    $prefix = substr(md5($this->password . mt_rand()*mt_rand()),rand(0,24), rand(6,8));
    $this->uniqueId = \uniqid($prefix);
  }
  
  /**
   * Set a uniqueid
   * Prefferably call generateUniqueId - but it can also be set manually
   * @param string $uniqueId;
   */
  public function setUniqueId($uniqueId){
    $this->uniqueId = $uniqueId;
  }
  
  /**
   * Get uniqueId
   *
   * @return string $uniqueId
   */
  public function getUniqueId(){
    return $this->uniqueId;
  }
  
  /**
   * Lock the Applicant
   */
  public function lock(){
    $this->isLocked = true;
    if(is_null($this->decision)){
      $this->decision = new Decision();
      $this->decision->setApplicant($this);
    } 
  }
  
  /**
   * UnLock the Applicant
   */
  public function unLock(){
    $this->isLocked = false;
  }

  /**
   * Get locked
   *
   * @return boolean $locked
   */
  public function isLocked(){
    return $this->isLocked;
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
   * Set middleName
   *
   * @param string $middleName
   */
  public function setMiddleName($middleName){
    $this->middleName = $middleName;
  }

  /**
   * Get middleName
   *
   * @return string $middleName
   */
  public function getMiddleName(){
    return $this->middleName;
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
   * Set suffix
   *
   * @param string $suffix
   */
  public function setSuffix($suffix){
    $this->suffix = $suffix;
  }

  /**
   * Get suffix
   *
   * @return string $suffix
   */
  public function getSuffix(){
    return $this->suffix;
  }
  
  /**
   * Get an applicants full name
   * 
   * Combines all the name parts nicely
   */
  public function getFullName(){
    $name = $this->firstName . ' ';
    if($this->middleName) $name .= $this->middleName;
    $name .= ' ' . $this->lastName;
    if($this->suffix) $name .= ' ' . $this->suffix;
    
    return $name;
  }
  
  /**
   * Set deadlineExtension
   *
   * @param string $deadlineExtension
   */
  public function setDeadlineExtension($deadlineExtension){
    $this->deadlineExtension = new \DateTime($deadlineExtension);
  }

  /**
   * Get deadlineExtension
   *
   * @return DateTime $deadlineExtension
   */
  public function getDeadlineExtension(){
    return $this->deadlineExtension;
  }
  
  /**
   * remove deadlineExtension
   */
  public function removeDeadlineExtension(){
    $this->deadlineExtension = null;
  }

  /**
   * Register a sucessfull login
   *
   */
  public function login(){
    $this->lastLogin = new \DateTime();
    $this->lastLoginIp = $_SERVER['REMOTE_ADDR'];
    $this->failedLoginAttempts = 0;
  }

  /**
   * set lastLogin
   *
   * @param string $lastLogin
   */
  public function setLastLogin($lastLogin){
    $this->lastLogin = new \DateTime($lastLogin);
  }

  /**
   * Get lastLogin
   *
   * @return \DateTime $lastLogin
   */
  public function getLastLogin(){
    return $this->lastLogin;
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
   * Get lastFailedLoginIp
   *
   * @return string $lastFailedLoginIp
   */
  public function getLastFailedLoginIp(){
    return $this->lastFailedLoginIp;
  }
  
  /**
   * Fail an applicant login
   */
  public function loginFail(){
    $this->lastFailedLoginIp = $_SERVER['REMOTE_ADDR'];
    $this->failedLoginAttempts++;
    
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
   * Set createdAt
   *
   * @param string $createdAt
   */
  public function setCreatedAt($createdAt){
    $this->createdAt = new \DateTime($createdAt);
  }

  /**
   * Get createdAt
   *
   * @return \DateTime $createdAt
   */
  public function getCreatedAt(){
    return $this->createdAt;
  }
  
  /**
   * Mark the lastUpdate automatically
   * @PrePersist
   */
  public function markLastUpdate(){
    if(!$this->updatedAtOveridden) $this->updatedAt = new \DateTime();
    $this->percentComplete = $this->calculatePercentComplete();
  }

  /**
   * Set updatedAt
   *
   * @param string $updatedAt
   */
  public function setUpdatedAt($updatedAt){
    $this->updatedAtOveridden = true;
    $this->updatedAt = new \DateTime($updatedAt);
  }

  /**
   * Get updatedAt
   *
   * @return \DateTime $updatedAt
   */
  public function getUpdatedAt(){
    return $this->updatedAt;
  }

  /**
   * Set application
   *
   * @param Entity\Application $application
   */
  public function setApplication(Application $application){
    $this->application = $application;
  }

  /**
   * Get application
   *
   * @return Entity\Application $application
   */
  public function getApplication(){
    return $this->application;
  }
  
  /**
   * Add answer
   *
   * @param \Jazzee\Entity\Answer $answer
   */
  public function addAnswer(\Jazzee\Entity\Answer $answer){
    $this->answers[] = $answer;
    if($answer->getApplicant() != $this) $answer->setApplicant($this);
  }
  
  /**
   * get all answers
   *
   * @param Doctrine\Common\Collections\Collection \Jazzee\Entity\Answer
   */
  public function getAnswers(){
    return $this->answers;
  }
  
  /**
   * Find answers for a page
   * 
   * @param \Jazzee\Entity\Page
   * @return array \Jazzee\Entity\Answer
   */
  public function findAnswersByPage(Page $page){
    $return = array();
    foreach($this->answers as $answer) if($answer->getPage() === $page) $return[] = $answer;
    return $return;
  }
  
  /**
   * Find answer by id
   * 
   * @param integer $id
   * @return \Jazzee\Entity\Answer or false
   */
  public function findAnswerById($id){
    foreach($this->answers as $answer) if($answer->getId() == $id) return $answer;
    return false;
  }

  /**
   * Add attachment
   *
   * @param \Jazzee\Entity\Attachment $attachment
   */
  public function addAttachment(Attachment $attachment){
    $this->attachments[] = $attachment;
    if($attachment->getApplicant() != $this) $attachment->setApplicant($this);
  }

  /**
   * Remove attachment
   *
   * @param \Jazzee\Entity\Attachment $attachment
   */
  public function removeAttachment(Attachment $attachment){
    $this->attachments->removeElement($attachment);
  }
  
  /**
   * Get attachments
   *
   * @return Doctrine\Common\Collections\Collection $attachments
   */
  public function getAttachments(){
    $attachments = array();
    foreach($this->attachments as $attachment) if($attachment->getAnswer() == null) $attachments[] = $attachment;
    return $attachments;
  }

  /**
   * Set decision
   *
   * @param Entity\Decision $decision
   */
  public function setDecision(Decision $decision){
    $this->decision = $decision;
  }

  /**
   * Get decision
   *
   * @return \Jazzee\Entity\Decision $decision
   */
  public function getDecision(){
    return $this->decision;
  }

  /**
   * Add tags
   *
   * @param Entity\Tag $tag
   */
  public function addTag(Tag $tag){
    $this->tags[] = $tag;
    $this->markLastUpdate();
  }

  /**
   * Remove tag
   *
   * @param Entity\Tag $tag
   */
  public function removeTag(Tag $tag){
    $this->tags->removeElement($tag);
    $this->markLastUpdate();
  }

  /**
   * Get tags
   *
   * @return Doctrine\Common\Collections\Collection $tags
   */
  public function getTags(){
    return $this->tags;
  }

  /**
   * Add thread
   *
   * @param \Jazzee\Entity\Thread $thread
   */
  public function addThread(Thread $thread){
    $this->threads[] = $thread;
    if($thread->getApplicant() != $this) $thread->setApplicant($this);
  }

  /**
   * Get threads
   *
   * @return Doctrine\Common\Collections\Collection $threads
   */
  public function getThreads(){
    return $this->threads;
  }

  /**
   * Get duplicates
   *
   * @return Doctrine\Common\Collections\Collection $duplicates
   */
  public function getDuplicates(){
    return $this->duplicates;
  }

  /**
   * Unread Message Count
   *
   * @return integer
   */
  public function unreadMessageCount(){
    $count = 0;
    foreach($this->threads as $thread)
      foreach($thread->getMessages() as $message) 
        if(!$message->isRead(\Jazzee\Entity\Message::PROGRAM)) $count++;
    return $count;
  }

  /**
   * Get the applicants percent complete
   * If it isn't set then generate it
   */
  public function getPercentComplete(){
    return $this->percentComplete;
  }
  
  protected function calculatePercentComplete(){
    $complete = 0;
    $pages = $this->application->getApplicationPages(\Jazzee\Entity\ApplicationPage::APPLICATION);
    foreach($pages as $pageEntity){
      $pageEntity->getJazzeePage()->setApplicant($this);
      if($pageEntity->getJazzeePage()->getStatus() == \Jazzee\Page::COMPLETE OR $pageEntity->getJazzeePage()->getStatus() == \Jazzee\Page::SKIPPED) $complete++;
    }
    //avoid division by 0 and dividing 0 by something
    if($complete == 0 or count($pages) == 0) return 0;
    
    return round($complete/count($pages), 2);
  }
}

/**
 * ApplicantRepository
 * Special Repository methods for Applicants
 */
class ApplicantRepository extends \Doctrine\ORM\EntityRepository{
  
  /**
   * find on by email address and application
   * 
   * Search for an Applicant by email in an application
   * @param string $email
   * @param Application $application
   * @return Application
   */
  public function findOneByEmailAndApplication($email, Application $application){
    $query = $this->_em->createQuery('SELECT a FROM Jazzee\Entity\Applicant a WHERE a.application = :applicationId AND  a.email = :email');
    $query->setParameter('applicationId', $application->getId());
    $query->setParameter('email', $email);
    $result = $query->getResult();
    if(count($result)) return $result[0];
    return false;
  }
  
  /**
   * find applicants in a cycle
   * 
   * Search for an Applicant by cycle
   * @param Cycle $cycle
   * @return array
   */
  public function findByCycle(Cycle $cycle){
    $query = $this->_em->createQuery('SELECT a FROM Jazzee\Entity\Applicant a WHERE a.application IN (SELECT app FROM Jazzee\Entity\Application app WHERE app.cycle = :cycleId)');
    $query->setParameter('cycleId', $cycle->getId());
    return $query->getResult();
  }
  
  /**
   * find duplicate applicants in the same cycle
   * 
   * @param Applicant $applicant
   * @return array
   */
  public function findDuplicates(Applicant $applicant){
    $query = $this->_em->createQuery('SELECT a FROM Jazzee\Entity\Applicant a WHERE a != :applicantId AND a.email = :email AND a.application IN (SELECT app FROM Jazzee\Entity\Application app WHERE app.cycle = :cycleId)');
    $query->setParameter('applicantId', $applicant->getId());
    $query->setParameter('cycleId', $applicant->getApplication()->getCycle()->getId());
    $query->setParameter('email', $applicant->getEmail());
    return $query->getResult();
  }
  
  /**
   * Find applicants by name
   * 
   * @param string $firstName
   * @param string $lastName
   * @param Application $application
   * @return Application
   */
  public function findApplicantsByName($firstName, $lastName, Application $application = null){
    $qb = $this->_em->createQueryBuilder();
    $qb->add('select', 'a')
     ->from('Jazzee\Entity\Applicant', 'a');
    
    if(!is_null($application)){
      $qb->where('a.application = :applicationId');
      $qb->setParameter('applicationId', $application->getId());
    }
    $qb->andWhere('a.firstName LIKE :firstName')
     ->andWhere('a.lastName LIKE :lastName')
     ->orderBy('a.lastName, a.firstName');
    $qb->setParameter('firstName', $firstName);
    $qb->setParameter('lastName', $lastName);
    
    return $qb->getQuery()->getResult();
  }
  
  /**
   * Reset all the failed login counter for applicants
   */
  public function resetFailedLoginCounters(){
    $query = $this->_em->createQuery('UPDATE Jazzee\Entity\Applicant a SET a.failedLoginAttempts = 0 WHERE a.failedLoginAttempts > 0');
    $query->execute();
  }
  
  /**
   * Reset all the unique ids
   */
  public function resetUniqueIds(){
    $query = $this->_em->createQuery('UPDATE Jazzee\Entity\Applicant a SET a.uniqueId = null WHERE a.uniqueId IS NOT NULL');
    $query->execute();
  }
}