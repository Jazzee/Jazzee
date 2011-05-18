<?php
namespace Entity;

/** 
 * Application
 * Cycle+Program=Application
 * Sets the unique preferences for a givien Cycle/Program and contains all of its Applicants
 * @Entity(repositoryClass="Entity\ApplicationRepository")
 * @Table(name="applications",uniqueConstraints={@UniqueConstraint(name="program_cycle", columns={"program_id", "cycle_id"})}) 
 * 
 * @package    jazzee
 * @subpackage orm
 **/
class Application{  
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** 
   * @ManyToOne(targetEntity="Program",cascade={"all"})
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE") 
   */
  private $program;
  
  /** 
   * @ManyToOne(targetEntity="Cycle",cascade={"all"})
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE")
   */
  private $cycle;
  
  /** 
   * @OneToMany(targetEntity="ApplicationPage", mappedBy="application")
   * @OrderBy({"weight" = "ASC"})
   */
  private $pages;
  
  /** @Column(type="string", nullable=true) */
  private $contactName;
  
  /** @Column(type="string", nullable=true) */
  private $contactEmail;
  
  /** @Column(type="text", nullable=true) */
  private $welcome;
  
  /** @Column(type="datetime", nullable=true) */
  private $open;
  
  /** @Column(type="datetime", nullable=true) */
  private $close;
  
  /** @Column(type="datetime", nullable=true) */
  private $begin;
  
  /** @Column(type="boolean", nullable=true) */
  private $published;
  
  /** @Column(type="boolean", nullable=true) */
  private $visible;
  
  /** @Column(type="text", nullable=true) */
  private $admitLetter;
  
  /** @Column(type="text", nullable=true) */
  private $denyLetter;
  
  /** @Column(type="text", nullable=true) */
  private $statusPageText;
  
  public function __construct(){
    $this->pages = new \Doctrine\Common\Collections\ArrayCollection();
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
   * Set contactName
   *
   * @param string $contactName
   */
  public function setContactName($contactName){
    $this->contactName = $contactName;
  }

  /**
   * Get contactName
   *
   * @return string $contactName
   */
  public function getContactName(){
    return $this->contactName;
  }

  /**
   * Set contactEmail
   *
   * @param string $contactEmail
   */
  public function setContactEmail($contactEmail){
    $this->contactEmail = $contactEmail;
  }

  /**
   * Get contactEmail
   *
   * @return string $contactEmail
   */
  public function getContactEmail(){
    return $this->contactEmail;
  }

  /**
   * Set welcome
   *
   * @param text $welcome
   */
  public function setWelcome($welcome){
    $this->welcome = $welcome;
  }

  /**
   * Get welcome
   *
   * @return text $welcome
   */
  public function getWelcome(){
    return $this->welcome;
  }

  /**
   * Set open
   *
   * @param string $open
   */
  public function setOpen($open){
    $this->open = new \DateTime($open);
  }

  /**
   * Get open
   *
   * @return datetime $open
   */
  public function getOpen(){
    return $this->open;
  }

  /**
   * Set close
   *
   * @param string $close
   */
  public function setClose($close){
    $this->close = new \DateTime($close);
  }

  /**
   * Get close
   *
   * @return DateTime $close
   */
  public function getClose(){
    return $this->close;
  }

  /**
   * Set begin
   *
   * @param string $begin
   */
  public function setBegin($begin){
    $this->begin = new \DateTime($begin);
  }

  /**
   * Get begin
   *
   * @return DateTime $begin
   */
  public function getBegin(){
    return $this->begin;
  }

  /**
   * Publish and application
   */
  public function publish(){
    $this->published = true;
  }
  
 /**
   * Un Publish and application
   */
  public function unPublish(){
    $this->published = false;
  }

  /**
   * Get published status
   * @return boolean $published
   */
  public function isPublished(){
    return $this->published;
  }

  /**
   * Make Application Visible
   */
  public function visible(){
    $this->visible = true;
  }
  
/**
   * Make Application InVisible
   */
  public function inVisible(){
    $this->visible = false;
  }

  /**
   * Get visible status
   *
   * @return boolean $visible
   */
  public function isVisible(){
    return $this->visible;
  }

  /**
   * Set admitLetter
   *
   * @param text $admitLetter
   */
  public function setAdmitLetter($admitLetter){
    $this->admitLetter = $admitLetter;
  }

  /**
   * Get admitLetter
   *
   * @return text $admitLetter
   */
  public function getAdmitLetter(){
    return $this->admitLetter;
  }

  /**
   * Set denyLetter
   *
   * @param text $denyLetter
   */
  public function setDenyLetter($denyLetter){
    $this->denyLetter = $denyLetter;
  }

  /**
   * Get denyLetter
   *
   * @return text $denyLetter
   */
  public function getDenyLetter(){
    return $this->denyLetter;
  }

  /**
   * Set statusPageText
   *
   * @param text $statusPageText
   */
  public function setStatusPageText($statusPageText){
    $this->statusPageText = $statusPageText;
  }

  /**
   * Get statusPageText
   *
   * @return text $statusPageText
   */
  public function getStatusPageText(){
    return $this->statusPageText;
  }

  /**
   * Set program
   *
   * @param Entity\Program $program
   */
  public function setProgram(Program $program){
    $this->program = $program;
  }

  /**
   * Get program
   *
   * @return Entity\Program $program
   */
  public function getProgram(){
    return $this->program;
  }

  /**
   * Set cycle
   *
   * @param Entity\Cycle $cycle
   */
  public function setCycle(Cycle $cycle){
    $this->cycle = $cycle;
  }

  /**
   * Get cycle
   *
   * @return Entity\Cycle $cycle
   */
  public function getCycle(){
    return $this->cycle;
  }

  /**
   * Add pages
   *
   * @param Entity\ApplicationPage $page
   */
  public function addPage(ApplicationPage $page){
    $this->pages[] = $page;
  }

  /**
   * Get pages
   *
   * @return Doctrine\Common\Collections\Collection $pages
   */
  public function getPages(){
    return $this->pages;
  }
}

/**
 * ApplicationRepository
 * Special Repository methods for Application to make searchign for special conditions easier
 */
class ApplicationRepository extends \Doctrine\ORM\EntityRepository{
  
  /**
   * findOneByProgramAndCycle
   * Search for an Application using its Program and Cycle
   * @param Program $program
   * @param Cycle $cycle
   * @return Application
   */
  public function findOneByProgramAndCycle(Program $program, Cycle $cycle){
    $query = $this->_em->createQuery('SELECT a FROM Entity\Application a WHERE a.program = :programId AND  a.cycle = :cycleId');
    $query->setParameter('programId', $program->getId());
    $query->setParameter('cycleId', $cycle->getId());
    $result = $query->getResult();
    if(count($result)) return $result[0];
    return false;
  }
  
/**
   * findByProgram
   * Search for all the Applications belonging to a program
   * @param Program $program
   * @return Doctrine\Common\Collections\Collection $applications
   */
  public function findByProgram(Program $program){
    $query = $this->_em->createQuery('SELECT a FROM Entity\Application a WHERE a.program = :programId');
    $query->setParameter('programId', $program->getId());
    return $query->getResult();
  }
}