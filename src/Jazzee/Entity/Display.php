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

  /** @Column(type="string", length=255) */
  private $name;

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
   * @OneToMany(targetEntity="DisplayElement", mappedBy="display")
   * @OrderBy({"weight" = "ASC"})
   */
  private $elements;

  public function __construct()
  {
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
   * Get a list or all the pages in the display for limiting
   * 
   * @return array
   */
  public function getPageIds()
  {
    $arr = array();
    foreach($this->elements as $displayElement){
      if($displayElement->getType() == 'page'){
        $arr[] = $displayElement->getElement()->getPage()->getId();
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
      $elements[] = new \Jazzee\Display\Element($displayElement->getType(), $displayElement->getTitle(), $displayElement->getWeight(), $displayElement->getName());
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

}