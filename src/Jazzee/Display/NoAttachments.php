<?php
namespace Jazzee\Display;

/**
 * Full Applicaiton display
 * A builtin display for showing the entire application 
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class NoAttachments extends FullApplication
{
  
  /**
   * Construcutor takes the application we want to display
   * @param \Jazzee\Entity\Application $application
   */
  public function __construct(\Jazzee\Entity\Application $application) {
    $applicantElements = array(
      'firstName' => 'First Name',
      'lastName' => 'Last Name',
      'email' => 'Email',
      'createdAt' => 'Created At',
      'updatedAt' => 'Updated At',
      'lastLogin' => 'Last Login',
      'percentComplete' => 'Progress',
      'isLocked' => 'Locked',
      'hasPaid' => 'Paid'
    );
    $elements = array();
    $weight = 0;
    foreach($applicantElements as $name => $title){
      $elements[] = new \Jazzee\Display\Element('applicant', $title, $weight++, $name);
    }
    $pages = array();
    foreach($application->getApplicationPages() as $applicationPage){
      if(is_subclass_of($applicationPage->getPage()->getType()->getClass(), 'Jazzee\Interfaces\DataPage')){
        $pageArr = array(
          'id' => $applicationPage->getPage()->getId(),
          'title' => $applicationPage->getTitle(),
          'elements' => array()
        );
        $this->_pageIds[] = $applicationPage->getPage()->getId();
        foreach($applicationPage->getPage()->getElements() as $element){
          if($element->getType()->getClass() != '\Jazzee\Element\PDFFileInput'){
            $pageArr['elements'][] = array(
              'id' => $element->getId(),
              'title' => $element->getTitle(),
            );
            $this->_elementIds[] = $element->getId();
            $this->_elements[] = new \Jazzee\Display\Element('element', $element->getTitle(), $weight++);
          }
        }
        $pages[] = $pageArr;
      }
    }
    $this->_displayArray['pages'] = $pages;
  }

  /**
   * Get the name of the display
   * 
   * @return string
   */
  public function getName(){
    return 'Full Application (no pdfs)';
  }

  /**
   * Get id
   * 
   * @return string
   */
  public function getId(){
    return 'noattachments';
  }
}