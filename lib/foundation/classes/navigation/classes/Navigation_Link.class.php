<?php
/**
 * A single Navigation link
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage navigation
 */
class Navigation_Link extends HTML_Element{
  /**
   * The Content of the Link
   * @var string
   */
  public $text;
  
  /**
   * Is this the current page
   */
  public $current = false;
  
  /**
   * HTML Attributes
   */
  public $charset;
  public $coords;
  public $href;
  public $hreflang;
  public $name;
  public $rel;
  public $rev;
  public $shape;
  
  /**
   * Constructor
   */
  public function __construct(){
    parent::__construct();
    $this->attributes['charset'] = 'charset';
    $this->attributes['coords'] = 'coords';
    $this->attributes['href'] = 'href';
    $this->attributes['hreflang'] = 'hreflang';
    $this->attributes['name'] = 'name';
    $this->attributes['rel'] = 'rel';
    $this->attributes['rev'] = 'rev';
    $this->attributes['shape'] = 'shape';
  }

}
?>