<?php
/**
 * Select element form control
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
 ?>
 <select <?php
foreach($element->getAttributes() as $memberName => $htmlName){
  $this->renderElement('attribute', array('name'=>$htmlName, 'value'=>$element->$memberName));
}
?>>
<?php foreach($element->getItems() as $item){
  echo '<option';
  if($element->value == $item->value){
    print ' selected="selected"';
  }
  foreach($item->getAttributes() as $memberName => $htmlName){
    $this->renderElement('attribute', array('name'=>$htmlName, 'value'=>$item->$memberName));
  }
  echo ">{$item->label}</option>";
}
?>
</select>