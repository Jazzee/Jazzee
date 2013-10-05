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
class DisplayElement implements \Jazzee\Interfaces\DisplayElement
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
   * Get name
   *
   * @return string
   */
  public function getPageId()
  {
    switch($this->type){
      case 'page':
        return $this->page->getId();
        break;
      case 'element':
        return $this->element->getPage()->getId();
        break;
      default:
        return null;
    }
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
   * @param \Jazzee\Interfaces\DisplayElement $originalElement
   * @param \Jazzee\Entity\Application $application
   * 
   * @return \Jazzee\Interfaces\DisplayElement
   */
  public static function createFromDisplayElement(\Jazzee\Interfaces\DisplayElement $originalElement, \Jazzee\Entity\Application $application)
  {
    $displayElement = new \Jazzee\Entity\DisplayElement($originalElement->getType());
    switch($originalElement->getType()){
      case 'element':
        if(!$element = $application->getElementById($originalElement->getName())){
          throw new \Jazzee\Exception("{$originalElement->getName()} is not a valid Jazzee Element ID, so it cannot be used in a 'element' display element.  Element: " . var_export($originalElement, true));
        }
        $displayElement->setElement($element);
        break;
      case 'page':
        if(!$applicationPage = $application->getApplicationPageByPageId($originalElement->getPageId()) and !$applicationPage = $application->getApplicationPageByChildPageId($originalElement->getPageId())){
          throw new \Jazzee\Exception("{$originalElement->getPageId()} is not a valid Page ID, so it cannot be used in a 'page' display element.  Element: " . var_export($originalElement, true));
        }
        $displayElement->setPage($applicationPage->getPage());
      case 'applicant':
        $displayElement->setName($originalElement->getName());
        break;
    }
    $displayElement->setTitle($originalElement->getTitle());
    $displayElement->setWeight($originalElement->getWeight()); 

    return $displayElement;
  }

  public function sameAs(\Jazzee\Interfaces\DisplayElement $element)
  {
    return ($this->type == $element->getType() and $this->name == $element->getName() and ((is_null($this->pageId) and is_null($element->getPageId())) or $this->pageId == $element->getPageId()));
  }
  
  public function getDisplayElementObject(){
    return new \Jazzee\Display\Element($this->getType(), $this->getTitle(), $this->getWeight(), $this->getName(), $this->getPageId());
  }

}