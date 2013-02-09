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
  private $isFirstNameDisplayed;

  /** @Column(type="boolean") */
  private $isLastNameDisplayed;

  /** @Column(type="boolean") */
  private $isEmailDisplayed;

  /** @Column(type="boolean") */
  private $isCreatedAtDisplayed;

  /** @Column(type="boolean") */
  private $isUpdatedAtDisplayed;

  /** @Column(type="boolean") */
  private $isLastLoginDisplayed;

  /** @Column(type="boolean") */
  private $isPercentCompleteDisplayed;

  /** @Column(type="boolean") */
  private $isHasPaidDisplayed;

  /** @Column(type="boolean") */
  private $isIsLockedDisplayed;

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
    $this->isFirstNameDisplayed = true;
    $this->isLastNameDisplayed = true;
    $this->isEmailDisplayed = true;
    $this->isCreatedAtDisplayed = true;
    $this->isUpdatedAtDisplayed = true;
    $this->isLastLoginDisplayed = true;
    $this->isPercentCompleteDisplayed = true;
    $this->isHasPaidDisplayed = true;
    $this->isIsLockedDisplayed = true;
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
   * Search for a DisplayPage for this Page
   * @param \Jazzee\Entity\Page $page
   * @return DisplayPage
   */
  public function getDisplayPageByPage(Page $page) {
    foreach($this->pages as $displayPage){
      if($displayPage->getApplicationPage()->getPage() == $page){
        return $displayPage;
      }
    }
    
    return false;
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
    $this->isCreatedAtDisplayed = true;
  }
  
  public function hideCreatedAt(){
    $this->isCreatedAtDisplayed = false;
  }

  public function isCreatedAtDisplayed() {
    return $this->isCreatedAtDisplayed;
  }
  
  public function showEmail(){
    $this->isEmailDisplayed = true;
  }
  
  public function hideEmail(){
    $this->isEmailDisplayed = false;
  }

  public function isEmailDisplayed() {
    return $this->isEmailDisplayed;
  }
  
  public function showFirstName(){
    $this->isFirstNameDisplayed = true;
  }
  
  public function hideFirstName(){
    $this->isFirstNameDisplayed = false;
  }

  public function isFirstNameDisplayed() {
    return $this->isFirstNameDisplayed;
  }
  
  public function showHasPaid(){
    $this->isHasPaidDisplayed = true;
  }
  
  public function hideHasPaid(){
    $this->isHasPaidDisplayed = false;
  }

  public function isHasPaidDisplayed() {
    return $this->isHasPaidDisplayed;
  }
  
  public function showLastLogin(){
    $this->isLastLoginDisplayed = true;
  }
  
  public function hideLastLogin(){
    $this->isLastLoginDisplayed = false;
  }

  public function isLastLoginDisplayed() {
    return $this->isLastLoginDisplayed;
  }
  
  public function showLastName(){
    $this->isLastNameDisplayed = true;
  }
  
  public function hideLastName(){
    $this->isLastNameDisplayed = false;
  }

  public function isLastNameDisplayed() {
    return $this->isLastNameDisplayed;
  }
  
  public function showPercentComplete(){
    $this->isPercentCompleteDisplayed = true;
  }
  
  public function hidePercentComplete(){
    $this->isPercentCompleteDisplayed = false;
  }

  public function isPercentCompleteDisplayed() {
    return $this->isPercentCompleteDisplayed;
  }
  
  public function showUpdatedAt(){
    $this->isUpdatedAtDisplayed = true;
  }
  
  public function hideUpdatedAt(){
    $this->isUpdatedAtDisplayed = false;
  }

  public function isUpdatedAtDisplayed() {
    return $this->isUpdatedAtDisplayed;
  }
  
  public function showIsLocked(){
    $this->isIsLockedDisplayed = true;
  }
  
  public function hideIsLocked(){
    $this->isIsLockedDisplayed = false;
  }

  public function isIsLockedDisplayed() {
    return $this->isIsLockedDisplayed;
  }

}