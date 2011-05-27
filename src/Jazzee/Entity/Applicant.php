<?php
namespace Jazzee\Entity;

/** 
 * Applicant
 * Individual applicants are tied to an Application - but a single person can be multiple Applicants
 * @Entity(repositoryClass="\Jazzee\Entity\ApplicantRepository") 
 * @Table(name="applicants",uniqueConstraints={@UniqueConstraint(name="application_email", columns={"application_id", "email"})}) 
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
  
  /** 
   * @ManyToOne(targetEntity="Application", inversedBy="applicants")
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE") 
   */
  private $application;
  
  /** @Column(type="string") */
  private $email;
  
  /** @Column(type="string") */
  private $password;
  
  /** @Column(type="datetime", nullable=true) */
  private $locked;
  
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
  
  /** 
   * @OneToMany(targetEntity="Answer",mappedBy="applicant")
   */
  private $answers;
  
  /** 
   * @OneToMany(targetEntity="Attachment",mappedBy="applicant")
   */
  private $attachments;
  
  /** 
   * @OneToOne(targetEntity="Decision",mappedBy="applicant")
   */
  private $decision;
  
  /** 
   * @OneToMany(targetEntity="Payment",mappedBy="applicant")
   */
  private $payments;
  
  /**
   * @ManyToMany(targetEntity="Tag", inversedBy="applicants")
   * @JoinTable(name="applicant_tags")
  **/
  private $tags;
  
  /** 
   * @OneToMany(targetEntity="Message",mappedBy="applicant") 
   */
  private $messages;
  
  public function __construct(){
    $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
    $this->attachments = new \Doctrine\Common\Collections\ArrayCollection();
    $this->payments = new \Doctrine\Common\Collections\ArrayCollection();
    $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
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
    $this->setPassword($password);
    $p = new \PasswordHash(8, FALSE);
    return $p->CheckPassword($password, $this->password);
  }
  
  /**
   * Lock the Applicant
   */
  public function lock(){
    $this->locked = new \DateTime('now');
  }
  
  /**
   * UnLock the Applicant
   */
  public function unLock(){
    $this->locked = null;
  }

  /**
   * Get locked
   *
   * @return boolean $locked
   */
  public function isLocked(){
    return $this->locked;
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
   * Register a sucessfull login
   *
   */
  public function login(){
    $this->lastLogin = new \DateTime();
    $this->lastLoginIp = $_SERVER['REMOTE_ADDR'];
    $this->failedLoginAttempts = 0;
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
   * Set updatedAt
   *
   * @param string $updatedAt
   */
  public function setUpdatedAt($updatedAt){
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
    if($answer->getApplicant() != $this) $answer->getApplicant($applicant);
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
    foreach($this->answers as $answer) if($answer->getPage() == $page) $return[] = $answer;
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
   * Get attachments
   *
   * @return Doctrine\Common\Collections\Collection $attachments
   */
  public function getAttachments(){
    return $this->attachments;
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
   * @return Entity\Decision $decision
   */
  public function getDecision(){
    return $this->decision;
  }

  /**
   * Add payments
   *
   * @param Entity\Payment $payment
   */
  public function addPayment(Payment $payment){
    $this->payments[] = $payment;
  }

  /**
   * Get payments
   *
   * @return Doctrine\Common\Collections\Collection $payments
   */
  public function getPayments(){
    return $this->payments;
  }

  /**
   * Add tags
   *
   * @param Entity\Tag $tag
   */
  public function addTag(Tag $tag){
    $this->tags[] = $tag;
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
   * Add messages
   *
   * @param Entity\Message $messages
   */
  public function addMessage(Message $message){
    $this->messages[] = $message;
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
}