<?php
/**
 * View for a link
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage navigation
 */
?>
<a<?
foreach($link->getAttributes() as $memberName => $htmlName){
  $this->renderElement('attribute', array('name'=>$htmlName, 'value'=>$link->$memberName));
}
?>><?php echo $link->text ?></a>
