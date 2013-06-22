<?php
namespace Jazzee\Entity;

/**
 * DisplayElement
 * Controls display variables for a single element
 *
 * @Entity
 * @Table(name="display_elements")
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class DisplayElement
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

  /**
   * @Column(type="string")
   */
  private $title;

  /**
   * @Column(type="string", nullable=true)
   */
  private $name;

  /** @Column(type="integer") */
  private $weight;

  /**
   * @ManyToOne(targetEntity="Display",inversedBy="elements")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $display;

  /**
   * @ManyToOne(targetEntity="Element")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $element;

  /**
   * @ManyToOne(targetEntity="Page")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $page;

  public function __construct($type)
  {
    if(!in_array($type, array('applicant', 'element', 'page'))){
      throw new \Jazzee\Exception("{$type} is not a valid type for DisplayElements");
    }
    $this->type = $type;
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
   * Set weight
   *
   * @param integer $weight
   */
  public function setWeight($weight)
  {
    $this->weight = $weight;
  }

  /**
   * Get weight
   *
   * @return integer $weight
   */
  public function getWeight()
  {
    return $this->weight;
  }

  /**
   * Set title
   *
   * @param string $title
   */
  public function setTitle($title)
  {
    $this->title = $title;
  }

  /**
   * Get title
   *
   * @return string
   */
  public function getTitle()
  {
    return $this->title;
  }

  /**
   * Set name
   *
   * @param string $name
   */
  public function setName($name)
  {
    if(!in_array($this->type, array('applicant', 'page'))){
      throw new \Jazzee\Exception("You cannot set name for DisplayElements that do not have the type 'applicant' or 'page'");
    }
    $this->name = $name;
  }

  /**
   * Get name
   *
   * @return string
   */
  public function getName()
  {
    switch($this->type){
      case 'applicant':
      case 'page':
        return $this->name;
        break;
      case 'element':
        return $this->element->getId();
        break;
    }
    
    throw new \Jazzee\Exception("Cannot get name for {$this->type} DisplayElement type");
  }

  /**
   * Get type
   *
   * @return string $type
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Get element
   *
   * @return Element
   */
  public function getElement()
  {
    return $this->element;
  }

  /**
   * Set element
   *
   * @param Element $element
   */
  public function setElement(Element $element)
  {
    if($this->type != 'element'){
      throw new \Jazzee\Excption("You cannot set Element for DisplayElements that do not have the type 'element'");
    }
    $this->element = $element;
    $this->name = $this->element->getId();
  }

  /**
   * Get page
   *
   * @return Page
   */
  public function getPage()
  {
    return $this->page;
  }

  /**
   * Set page
   *
   * @param Page $page
   */
  public function setPage(Page $page)
  {
    if($this->type != 'page'){
      throw new \Jazzee\Excption("You cannot set Page for DisplayElements that do not have the type 'page'");
    }
    $this->page = $page;
  }

  /**
   * Get display
   *
   * @return \Jazzee\Entity\Display
   */
  public function getDisplay()
  {
    return $this->display;
  }
  
  /**
   * Set the display
   * 
   * @param \Jazzee\Entity\Display $display
   */
  public function setDisplay(\Jazzee\Entity\Display $display)
  {
    $this->display = $display;
  }
  
  /**
   * Create a new display element from the Elements of another display
   * @param \Jazzee\Display\Element $originalElement
   * @param \Jazzee\Entity\Application $application
   * 
   * @return \Jazzee\Entity\DisplayElement
   */
  public static function createFromDisplayElement(\Jazzee\Display\Element $originalElement, \Jazzee\Entity\Application $application)
  {
    $displayElement = new \Jazzee\Entity\DisplayElement($originalElement->type);
    switch($originalElement->type){
      case 'element':
        if(!$element = $application->getElementById($originalElement->name)){
          throw new \Jazzee\Exception("{$originalElement->name} is not a valid Jazzee Element ID, so it cannot be used in a 'element' display element.  Element: " . var_export($originalElement, true));
        }
        $displayElement->setElement($element);
        break;
      case 'page':
        if(!$applicationPage = $application->getApplicationPageByPageId($originalElement->pageId) and !$applicationPage = $application->getApplicationPageByChildPageId($originalElement->pageId)){
          throw new \Jazzee\Exception("{$originalElement->pageId} is not a valid Page ID, so it cannot be used in a 'page' display element.  Element: " . var_export($originalElement, true));
        }
        $displayElement->setPage($applicationPage->getPage());
      case 'applicant':
        $displayElement->setName($originalElement->name);
        break;
    }
    $displayElement->setTitle($originalElement->title);
    $displayElement->setWeight($originalElement->weight); 

    return $displayElement;
  }

}