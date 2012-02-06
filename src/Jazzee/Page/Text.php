<?php
namespace Jazzee\Page;
/**
 * A page with no form just text
 */
class Text extends Standard {
  const APPLY_PAGE_ELEMENT = 'Text-apply_page';
  const APPLICANTS_SINGLE_ELEMENT = '';
  const APPLY_STATUS_ELEMENT = '';
  const PAGEBUILDER_SCRIPT = 'resource/scripts/page_types/JazzeePageText.js';
  
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