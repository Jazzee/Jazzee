<?php 
/**
 * apply_page view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
if($applicant->getDeadlineExtension() and $applicant->getDeadlineExtension() > $applicant->getApplication()->getClose()){
  $deadline = $applicant->getDeadlineExtension();
} else {
  $deadline = $applicant->getApplication()->getClose();
}

$layoutContentTop = "<p class='deadline";
if($applicant->getApplication()->getClose()->diff(new DateTime('today'))->days < 7){ $layoutContentTop .= ' approaching-deadline';}
$layoutContentTop .= "'>Application Deadline: " . $deadline->format('m/d/Y g:ia T') . '</p>';

$layoutContentTop .= '<p class="links"><a href="' . $this->path('apply/' . $applicant->getApplication()->getProgram()->getShortName() . '/' . $applicant->getApplication()->getCycle()->getName() . '/support') . '">Support</a>';
if($count = $applicant->unreadMessageCount()) $layoutContentTop .= '<sup class="count">' . $count . '</sup>';
$layoutContentTop .= '<a href="' . $this->path('apply/' . $applicant->getApplication()->getProgram()->getShortName() . '/' . $applicant->getApplication()->getCycle()->getName() . '/applicant/logout') . '">Log Out</a></p>';
$this->controller->setLayoutVar('layoutContentTop', $layoutContentTop);    
$elementName = \Foundation\VC\Config::findElementCacading($page->getPage()->getType()->getClass(), '', '-page');
$this->renderElement($elementName, array('page'=>$page, 'currentAnswerID'=>$currentAnswerID, 'applicant'=>$applicant));