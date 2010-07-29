<?php
/**
 * Input element form control
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
 ?>
 <!-- MAX_FILE_SIZE -->
 <input type="hidden" name="MAX_FILE_SIZE" value="<?php print $element->maxSize ?>" />
 
 <input<?php
foreach($element->getAttributes() as $memberName => $htmlName){
  $this->renderElement('attribute', array('name'=>$htmlName, 'value'=>$element->$memberName));
}
?>/>