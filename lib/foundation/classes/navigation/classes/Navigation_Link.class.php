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
    $this->_attributes['charset'] = 'charset';
    $this->_attributes['coords'] = 'coords';
    $this->_attributes['href'] = 'href';
    $this->_attributes['hreflang'] = 'hreflang';
    $this->_attributes['name'] = 'name';
    $this->_attributes['rel'] = 'rel';
    $this->_attributes['rev'] = 'rev';
    $this->_attributes['shape'] = 'shape';
  }

}
?>