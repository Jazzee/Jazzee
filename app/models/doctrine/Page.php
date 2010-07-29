<?php

/**
 * Page
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 */
class Page extends BasePage{
  public function findElementByTitle($title){
    foreach($this['Elements'] as $element){
      if($element->title == $title){return $element;}
    }
    return self::$_null;
  }
}