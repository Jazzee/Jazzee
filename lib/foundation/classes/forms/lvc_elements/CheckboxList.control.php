<?php
/**
 * Checkbox element form control
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
 ?>
 <ol>
<?php foreach($element->getItems() as $id => $item){
  echo "<li><input type='checkbox'";
  if(in_array($item->value,$element->value)){
    print ' checked="checked"';
  }
  foreach($item->getAttributes() as $memberName => $htmlName){
    $this->renderElement('attribute', array('name'=>$htmlName, 'value'=>$item->$memberName));
  }
  echo " name='{$element->name}[]' id='{$element->name}_{$id}' />" .
      "<label for='{$element->name}_{$id}'>{$item->label}</label>" .
      "</li>";
}

?>
</ol>