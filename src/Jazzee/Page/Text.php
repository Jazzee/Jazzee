<?php
namespace Jazzee\Page;
/**
 * A page with no form just text
 * 
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage pages
 */
class Text implements \Jazzee\Interfaces\Page {
  
 /**
  * The ApplicationPage Entity
  * @var \Jazzee\Entity\ApplicationPage
  */
  protected $_applicationPage;
    
  /**
   * Our controller
   * @var \Jazzee\Controller
   */
  protected $_controller;
  
  /**
   * The Applicant
   * @var \Jazzee\Entity\Applicant
   */
  protected $_applicant;
  
 /**
  * Contructor
  * 
  * @param \Jazzee\Entity\ApplicationPage $applicationPage
  */
  public function __construct(\Jazzee\Entity\ApplicationPage $applicationPage){
    $this->_applicationPage = $applicationPage;
  }
  
  /**
   * 
   * @see Jazzee.Page::setController()
   */
  public function setController(\Jazzee\Controller $controller){
    $this->_controller = $controller;
  }
  
  /**
   * 
   * @see Jazzee.Page::setApplicant()
   */
  public function setApplicant(\Jazzee\Entity\Applicant $applicant){
    $this->_applicant = $applicant;
  }
  
  /**
   * TextPages are always complete
   */
  public function getStatus(){
    return self::COMPLETE;
  }
  
  public static function applyPageElement(){
    return 'Text-apply_page';
  }
  
  public static function pageBuilderScriptPath(){
    return 'resource/scripts/page_types/JazzeePageText.js';
  }
  
  /**
   * No Special setup
   * @return null
   */
  public function setupNewPage(){
    return;
  }
  
  /**
   * By default just set the varialbe dont check it
   * @param string $name
   * @param string $value 
   */
  public function setVar($name, $value){
    $var = $this->_applicationPage->getPage()->setVar($name, $value);
    $this->_controller->getEntityManager()->persist($var);
  }
}
?>