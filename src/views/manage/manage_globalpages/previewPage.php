<?php 
/**
 * manage_globalpages previewPage view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
if($page){
  $elementName = \Foundation\VC\Config::findElementCacading($page->getPage()->getType()->getClass(), '', '-form');
  $this->renderElement($elementName, array('page'=>$page));
}
?>