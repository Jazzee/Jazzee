<?php
namespace Jazzee\Entity;

/**
 * Display
 * 
 * Format the display of applicant data
 *
 * @Entity(repositoryClass="\Jazzee\Entity\DisplayRepository")
 * @Table(name="displays")
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Display implements \Jazzee\Interfaces\Display
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /** @Column(type="array") */
  private $attributes;

  /** @Column(type="string", length=255) */
  private $name;

  /** @Column(type="boolean") */
  private $showFirstName;

  /** @Column(type="boolean") */
  private $showLastName;

  /** @Column(type="boolean") */
  private $showEmail;

  /** @Column(type="boolean") */
  private $showCreatedAt;

  /** @Column(type="boolean") */
  private $showUpdatedAt;

  /** @Column(type="boolean") */
  private $showLastLogin;

  /** @Column(type="boolean") */
  private $showPercentComplete;

  /** @Column(type="boolean") */
  private $showHasPaid;

  /**
   * @ManyToOne(targetEntity="User",inversedBy="displays")
   * @JoinColumn(onDelete="CASCADE")
   */
  protected $user;

  /**
   * @ManyToOne(targetEntity="Application")
   * @JoinColumn(onDelete="CASCADE")
   */
  protected $application;

  /**
   * @OneToMany(targetEntity="DisplayPage",mappedBy="display")
   */
  protected $pages;

  public function __construct()
  {
    $this->pages = new \Doctrine\Common\Collections\ArrayCollection();
    $this->attributes = array();
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
   * Get the name
   * 
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set the name
   * 
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * Set user
   *
   * @param User $user
   */
  public function setUser(User $user)
  {
    $this->user = $user;
  }

  /**
   * Get user
   *
   * @return Entity\User $user
   */
  public function getUser()
  {
    return $this->user;
  }
  
  /**
   * Set the application
   * 
   * @param Application $application
   */
  public function setApplication(Application $application){
    $this->application = $application;
  }

  /**
   * Get application
   *
   * @return Application
   */
  public function getApplication()
  {
    return $this->application;
  }

  /**
   * Add page
   *
   * @param DisplayPage $page
   */
  public function addPage(DisplayPage $page)
  {
    $this->pages[] = $page;
    if ($page->getDisplay() != $this) {
      $page->setDisplay($this);
    }
  }

  /**
   * Get pages
   *
   * @return array DisplayPage
   */
  public function getPages()
  {
    return $this->pages;
  }

  /**
   * Set attributes
   * @param array $attributes
   */
  public function setAttributes(array $attributes)
  {
    $this->attributes = $attributes;
  }

  /**
   * Get attributes
   *
   * @return string
   */
  public function getAttributes()
  {
    return $this->attributes;
  }
  
  /**
   * Get a list or all the pages in the display for limiting
   * 
   * @return array
   */
  public function getPageIds(){
    $arr = array();
    foreach($this->pages as $displayPage){
      $arr[] = $displayPage->getApplicationPage()->getPage()->getId();
    }
    
    return $arr;
  }
  
  /**
   * Get a list or all the elements in the display for limiting
   * 
   * @return array
   */
  public function getElementIds() {
    $arr = array();
    foreach($this->pages as $displayPage){
      $arr = array_merge($arr, $displayPage->getElementIds());
    }
    
    return $arr;
  }
  
  public function displayPage(Page $page) {
    foreach($this->pages as $displayPage){
      if($displayPage->getApplicationPage()->getPage() == $page){
        return true;
      }
    }
    return false;
  }
  
  public function displayElement(Element $element) {
    foreach($this->pages as $displayPage){
      foreach($displayPage->getElements() as $displayElement){
        if($displayElement->getElement() == $element){
          return true;
        }
      }
    }
    
    return false;
  }
  
  public function showCreatedAt(){
    $this->showCreatedAt = true;
  }
  
  public function hideCreatedAt(){
    $this->showCreatedAt = false;
  }

  public function isCreatedAtDisplayed() {
    return $this->showCreatedAt;
  }
  
  public function showEmail(){
    $this->showEmail = true;
  }
  
  public function hideEmail(){
    $this->showEmail = false;
  }

  public function isEmailDisplayed() {
    return $this->showEmail;
  }
  
  public function showFirstName(){
    $this->showFirstName = true;
  }
  
  public function hideFirstName(){
    $this->showFirstName = false;
  }

  public function isFirstNameDisplayed() {
    return $this->showFirstName;
  }
  
  public function showHasPaid(){
    $this->showHasPaid = true;
  }
  
  public function hideHasPaid(){
    $this->showHasPaid = false;
  }

  public function isHasPaidDisplayed() {
    return $this->showHasPaid;
  }
  
  public function showLastLogin(){
    $this->showLastLogin = true;
  }
  
  public function hideLastLogin(){
    $this->showLastLogin = false;
  }

  public function isLastLoginDisplayed() {
    return $this->showLastLogin;
  }
  
  public function showLastName(){
    $this->showLastName = true;
  }
  
  public function hideLastName(){
    $this->showLastName = false;
  }

  public function isLastNameDisplayed() {
    return $this->showLastName;
  }
  
  public function showPercentComplete(){
    $this->showPercentComplete = true;
  }
  
  public function hidePercentComplete(){
    $this->showPercentComplete = false;
  }

  public function isPercentCompleteDisplayed() {
    return $this->showPercentComplete;
  }
  
  public function showUpdatedAt(){
    $this->showUpdatedAt = true;
  }
  
  public function hideUpdatedAt(){
    $this->showUpdatedAt = false;
  }

  public function isUpdatedAtDisplayed() {
    return $this->showUpdatedAt;
  }

}