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
  private $isFirstNameDislayed;

  /** @Column(type="boolean") */
  private $isLastNameDislayed;

  /** @Column(type="boolean") */
  private $isEmailDislayed;

  /** @Column(type="boolean") */
  private $isCreatedAtDislayed;

  /** @Column(type="boolean") */
  private $isUpdatedAtDislayed;

  /** @Column(type="boolean") */
  private $isLastLoginDislayed;

  /** @Column(type="boolean") */
  private $isPercentCompleteDislayed;

  /** @Column(type="boolean") */
  private $isHasPaidDislayed;

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
    $this->isFirstNameDislayed = true;
    $this->isLastNameDislayed = true;
    $this->isEmailDislayed = true;
    $this->isCreatedAtDislayed = true;
    $this->isUpdatedAtDislayed = true;
    $this->isLastLoginDislayed = true;
    $this->isPercentCompleteDislayed = true;
    $this->isHasPaidDislayed = true;
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
    $this->isCreatedAtDislayed = true;
  }
  
  public function hideCreatedAt(){
    $this->isCreatedAtDislayed = false;
  }

  public function isCreatedAtDisplayed() {
    return $this->isCreatedAtDislayed;
  }
  
  public function showEmail(){
    $this->isEmailDislayed = true;
  }
  
  public function hideEmail(){
    $this->isEmailDislayed = false;
  }

  public function isEmailDisplayed() {
    return $this->isEmailDislayed;
  }
  
  public function showFirstName(){
    $this->isFirstNameDislayed = true;
  }
  
  public function hideFirstName(){
    $this->isFirstNameDislayed = false;
  }

  public function isFirstNameDisplayed() {
    return $this->isFirstNameDislayed;
  }
  
  public function showHasPaid(){
    $this->isHasPaidDislayed = true;
  }
  
  public function hideHasPaid(){
    $this->isHasPaidDislayed = false;
  }

  public function isHasPaidDisplayed() {
    return $this->isHasPaidDislayed;
  }
  
  public function showLastLogin(){
    $this->isLastLoginDislayed = true;
  }
  
  public function hideLastLogin(){
    $this->isLastLoginDislayed = false;
  }

  public function isLastLoginDisplayed() {
    return $this->isLastLoginDislayed;
  }
  
  public function showLastName(){
    $this->isLastNameDislayed = true;
  }
  
  public function hideLastName(){
    $this->isLastNameDislayed = false;
  }

  public function isLastNameDisplayed() {
    return $this->isLastNameDislayed;
  }
  
  public function showPercentComplete(){
    $this->isPercentCompleteDislayed = true;
  }
  
  public function hidePercentComplete(){
    $this->isPercentCompleteDislayed = false;
  }

  public function isPercentCompleteDisplayed() {
    return $this->isPercentCompleteDislayed;
  }
  
  public function showUpdatedAt(){
    $this->isUpdatedAtDislayed = true;
  }
  
  public function hideUpdatedAt(){
    $this->isUpdatedAtDislayed = false;
  }

  public function isUpdatedAtDisplayed() {
    return $this->isUpdatedAtDislayed;
  }

}