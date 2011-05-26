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
  * The input
  * @param mixed $value
  */
 protected $_value;
 
 /**
  * Contructor
  * 
  * @param \Jazzee\Entity\Element
  */
  public function __construct(\Jazzee\Entity\Element $element){
    $this->_element = $element;
  }
}
?>