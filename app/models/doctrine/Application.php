<?php
/**
 * Application
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 */
class Application extends BaseApplication{  
  /**
  * Get page by ID
  * @param integer $pageID
  * @return ApplicationPage
  */
  public function getPageByID($pageID){
    $key = array_search($pageID, $this->Pages->getPrimaryKeys());
    if($key !== false){ //use === becuase 0 is returned often
      return $this->Pages->get($key);
    }
    return false;
  }
  
 /**
  * Get page by its global ID
  * @param integer $globalPageID
  * @return ApplicationPage
  */
  public function getApplicationPageByGlobalID($globalPageID){
    foreach($this->Pages as $applicationPage){
      if($globalPageID == $applicationPage->pageID) return $applicationPage;
    }
    return false;
  }
  
  /**
   * Get applicant by ID
   * @param integer $id
   * @return Applicant
   */
  public function getApplicantByID($id){
    $key = array_search($id, $this->Applicants->getPrimaryKeys());
    if($key !== false){ //use === becuase 0 is returned often
      return $this->Applicants->get($key);
    }
    return false;
  }
  
  /**
   * After we save the application make sure all of its pages are properly saved too
   * At some point doctrine is unable to follow the relationships deep enough
   * This method explicitly saves the members of collections with the correct id
   */
  public function postSave(){
    foreach($this->Pages as $page){
      if($page->isModified(true)){
        $page->applicationID = $this->id;
        $page->save();
      }
    }
  }
}