<?php 
/**
 * apply_page view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
if($answers = $page->getJazzeePage()->getAnswers()){
  print "<div id='answers'>";
  $elementName = FoundationVC_Config::findElementCacading(get_class($page), '', '-answer');
  foreach($answers as $answer){
    $this->renderElement($elementName, array('answer'=>$answer, 'page'=>$page,'currentAnswerID'=>$currentAnswerID));
  }
  print '</div>';
}
$elementName = \Foundation\VC\Config::findElementCacading($page->getPage()->getType()->getClass(), '', '-form');
$this->renderElement($elementName, array('page'=>$page));