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
}
?>