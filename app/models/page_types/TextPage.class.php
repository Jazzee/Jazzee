<?php
/**
 * A page with no form just text
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
class TextPage extends StandardPage {
  const SHOW_PAGE = false;
  /**
   * Text pages dont have forms
   */
  protected function makeForm(){
    return false;
  }
  
  /**
   * Nothign is entered by applicants so nothing is displayed
   */
  public function showPageData(){
    return false;
  }
  
  /**
   * TextPages are always complete
   */
  public function getStatus(){
    return self::COMPLETE;
  }
}
?>