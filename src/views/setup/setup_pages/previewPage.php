<?php 
/**
 * setup_pages previewPage view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
?>
<div id='doc3'>
  <?php 
    $elementName = \Foundation\VC\Config::findElementCacading($page->getPage()->getType()->getClass(), '', '-page');
    $this->renderElement($elementName, array('page'=>$page, 'currentAnswerID'=>null));
?>
</div>