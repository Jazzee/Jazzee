<?php
namespace Jazzee\Page;
/**
 * A page with no form just text
 */
class Text extends Standard {
  
  /**
   * (non-PHPdoc)
   * @see Jazzee.Page::showReviewPage()
   */
  public function showReviewPage(){
    return false;
  }
  
  /**
   * Text pages dont have forms
   */
  protected function makeForm(){
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