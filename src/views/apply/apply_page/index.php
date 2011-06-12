<?php 
/**
 * apply_page view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
$elementName = \Foundation\VC\Config::findElementCacading($page->getPage()->getType()->getClass(), '', '-page');
$this->renderElement($elementName, array('page'=>$page, 'currentAnswerID'=>$currentAnswerID, 'applicant'=>$applicant));