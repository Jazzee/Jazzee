<?php
namespace Jazzee\Display;

/**
 * Convienience class for ensuring display elements created programatically have
 * all the necessary pieces
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Element
{ 
  /**
   *
   * @var string
   */
  public $type;
  
  /**
   *
   * @var string
   */
  public $title;
  
  /**
   *
   * @var integer
   */
  public $weight;
  
  /**
   *
   * @var string
   */
  public $name;
  
  /**
   *
   * @var string
   */
  public $pageId;
  
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
}