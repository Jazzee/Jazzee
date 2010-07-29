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
   * Get an application by shortname/cycle
   * @params string $shortName
   * @param string $cycleName
   * @return Application|false on failure
   */
  static function findOneApplication($shortName, $cycleName){
    $q = Doctrine_Query::create()
      ->from('Application a')
      ->leftJoin('a.Pages pages')
      ->leftJoin('a.Program program')
      ->leftJoin('a.Cycle cycle')
      ->where('program.shortName = ?', $shortName)
      ->andwhere('cycle.name = ?', $cycleName)
      ->orderby('pages.weight')
      ->limit(1);
    return $q->execute()->getFirst();
  }
  
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
}