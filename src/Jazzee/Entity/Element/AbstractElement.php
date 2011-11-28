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
  * Contructor
  * 
  * @param \Jazzee\Entity\Element
  */
  public function __construct(\Jazzee\Entity\Element $element){
    $this->_element = $element;
  }
  
  public function rawValue(\Jazzee\Entity\Answer $answer){
    return html_entity_decode($this->displayValue($answer));
  }
  
  public function pdfValue(\Jazzee\Entity\Answer $answer, \Jazzee\ApplicantPDF $pdf){
    return $this->rawValue($answer);
  }
}
?>