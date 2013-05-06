<?php
namespace Jazzee\Element;

/**
 * Select List Element
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class SearchList extends SelectList
{

  const PAGEBUILDER_SCRIPT = 'resource/scripts/element_types/JazzeeElementSearchList.js';

  public function addToField(\Foundation\Form\Field $field)
  {
    $element = $field->newElement('SearchList', 'el' . $this->_element->getId());
    $element->setLabel($this->_element->getTitle());
    $element->setInstructions($this->_element->getInstructions());
    $element->setFormat($this->_element->getFormat());
    $element->setDefaultValue($this->_element->getDefaultValue());
    if ($this->_element->isRequired()) {
      $validator = new \Foundation\Form\Validator\NotEmpty($element);
      $element->addValidator($validator);
    }
    $element->newItem('', '');
    foreach ($this->_element->getListItems() as $item) {
      if ($item->isActive()) {
        $listItem = $element->newItem($item->getId(), $item->getValue());
        $listItem->addMetadata($item->getVar('searchTerms'));
      }
    }
    $this->_controller->addScript($this->_controller->path('resource/foundation/scripts/SearchListElement.js'));

    return $element;
  }

}