<?php
namespace Jazzee\Entity\Element;
/**
 * The Abstract Application Elements
 */
abstract class AbstractElement implements \Jazzee\Element {
 
 /**
  * The Element entity
  * @var \Jazzee\Entity\Element
  */
 protected $_element;
 
 /**
  * The controller that is using this
  * @var \Jazzee\Controller
  */
 protected $_controller;
 
  public function __construct(\Jazzee\Entity\Element $element){
    $this->_element = $element;
  }
  
  public function setController(\Jazzee\Controller $controller){
    $this->_controller = $controller;
  }
  
  public function rawValue(\Jazzee\Entity\Answer $answer){
    return html_entity_decode($this->displayValue($answer));
  }
  
  public function pdfValue(\Jazzee\Entity\Answer $answer, \Jazzee\ApplicantPDF $pdf){
    return $this->rawValue($answer);
  }
}
?>