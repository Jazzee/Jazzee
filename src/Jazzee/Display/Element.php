<?php
namespace Jazzee\Display;

/**
 * Convienience class for ensuring display elements created programatically have
 * all the necessary pieces
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Element implements \Jazzee\Interfaces\DisplayElement
{ 
  /**
   *
   * @var string
   */
  protected $type;
  
  /**
   *
   * @var string
   */
  protected $title;
  
  /**
   *
   * @var integer
   */
  protected $weight;
  
  /**
   *
   * @var string
   */
  protected $name;
  
  /**
   *
   * @var string
   */
  protected $pageId;
  
  public function __construct($type, $title, $weight, $name, $pageId)
  {
    if(!in_array($type, array('applicant', 'element', 'page'))){
      throw new \Jazzee\Exception("{$type} is not a valid type for Jazzee\Display\Elements");
    }
    $this->type = $type;
    $this->title = $title;
    $this->weight = $weight;
    $this->name = $name;
    $this->pageId = $pageId;
  }
  
  /**
   * Check if this Element is the same as another one
   * @param \Jazzee\Interfaces\DisplayElement $element
   * 
   * @return type
   */
  public function sameAs(\Jazzee\Interfaces\DisplayElement $element){
    return ($this->type == $element->getType() and $this->name == $element->getName() and ((is_null($this->pageId) and is_null($element->getPageId())) or $this->pageId == $element->getPageId()));
  }

    public function getName()
    {
        return $this->name;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    public function getPageId()
    {
        return $this->pageId;
    }
}