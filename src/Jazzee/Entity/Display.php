<?php
namespace Jazzee\Entity;

/**
 * Display
 * 
 * Format the display of applicant data
 *
 * @Entity
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

  /**
   * @Column(type="string")
   */
  private $type;

  /** @Column(type="string", length=255) */
  private $name;

  /**
   * @ManyToOne(targetEntity="User",inversedBy="displays")
   * @JoinColumn(onDelete="CASCADE")
   */
  protected $user;

  /**
   * @OneToOne(targetEntity="Role",inversedBy="display")
   * @JoinColumn(onDelete="CASCADE")
   */
  protected $role;

  /**
   * @ManyToOne(targetEntity="Application")
   * @JoinColumn(onDelete="CASCADE")
   */
  protected $application;

  /**
   * @OneToMany(targetEntity="DisplayElement", mappedBy="display")
   * @OrderBy({"weight" = "ASC"})
   */
  private $elements;

  /**
   * Constructur requires the role type
   * @param string $type
   */
  public function __construct($type)
  {
    if(!in_array($type, array('user', 'role'))){
      throw new \Jazzee\Exception("{$type} is not a valid type for Display");
    }
    $this->type = $type;
    $this->elements = new \Doctrine\Common\Collections\ArrayCollection();
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
    if(!in_array($this->type, array('user'))){
      throw new \Jazzee\Exception("You cannot set user for Displays that do not have the type 'user'");
    }
    $this->user = $user;
  }

  /**
   * Get user
   *
   * @return User $user
   */
  public function getUser()
  {
    return $this->user;
  }

  /**
   * Set role
   *
   * @param Role $role
   */
  public function setRole(Role $role)
  {
    if(!in_array($this->type, array('role'))){
      throw new \Jazzee\Exception("You cannot set role for Displays that do not have the type 'role'");
    }
    if($role->isGlobal()){
      throw new \Jazzee\Exception("Global roles cannot be used for displays");
    }
    if(isset($this->application) and $this->application->getProgram()->getId() != $role->getProgram()->getId()){
      throw new \Jazzee\Exception("Role program / display program mismatch");
    }
    $this->role = $role;
  }

  /**
   * Get role
   *
   * @return Role $role
   */
  public function getRole()
  {
    return $this->role;
  }
  
  /**
   * Set the application
   * 
   * @param Application $application
   */
  public function setApplication(Application $application){
    if(isset($this->role) and $this->role->getProgram()->getId() != $application->getProgram()->getId()){
      throw new \Jazzee\Exception("Role program / display program mismatch");
    }
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
   * Get a list or all the pages in the display for limiting
   * 
   * @return array
   */
  public function getPageIds()
  {
    $arr = array();
    foreach($this->elements as $displayElement){
      if($displayElement->getType() == 'element'){
        $page = $displayElement->getElement()->getPage();
        while($page != null) {  
          $arr[] = $page->getId();
          $page = $page->getParent();
        }
      }
      if($displayElement->getType() == 'page'){
        $page = $displayElement->getPage();
        while($page != null) {  
          $arr[] = $page->getId();
          $page = $page->getParent();
        }
      }
    }

    return array_unique($arr);
  }

  /**
   * Add element
   *
   * @param DisplayElement $element
   */
  public function addElement(DisplayElement $element)
  {
    $this->elements[] = $element;
    if ($element->getDisplay() != $this) {
      $element->setDisplay($this);
    }
  }

  /**
   * Get DisplayElement elements
   *
   * @return array DisplayElement
   */
  public function getElements()
  {
    return $this->elements;
  }

  /**
   * List elements as an array
   *
   * @return array \Jazzee\Display\Element
   */
  public function listElements()
  {
    $elements = array();
    foreach($this->elements as $displayElement){
      $elements[] = new \Jazzee\Display\Element($displayElement->getType(), $displayElement->getTitle(), $displayElement->getWeight(), $displayElement->getName(), $displayElement->getPage()?$displayElement->getPage()->getId():null);
    }

    return $elements;
  }

  /**
   * Get elements
   *
   * @return array
   */
  public function getElementIds()
  {
    $ids = array();
    foreach($this->elements as $element){
      if($element->getType() == 'element'){
        $ids[] = $element->getElement()->getId();
      }
    }

    return $ids;
  }
  
  public function displayPage(Page $page)
  {
    $pageIds = $this->getPageIds();
    return in_array($page->getId(), $pageIds);
  }
  
  public function displayElement(Element $element)
  {
    $elementIds = $this->getElementIds();
    return in_array($element->getId(), $elementIds);
  }

  public function hasDisplayElement(\Jazzee\Display\Element $displayElement)
  {
    foreach($this->listElements() as $element){
      if($displayElement->sameAs($element)){
        return true;
      }
    }

    return false;
  }

}