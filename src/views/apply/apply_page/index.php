<?php 
/**
 * apply_page view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
?>
<?php
if($answers = $page->getJazzeePage()->getAnswers()){
  print "<div id='answers'>";
  $elementName = \Foundation\VC\Config::findElementCacading($page->getPage()->getType()->getClass(), '', '-answer');
  foreach($answers as $answer){
    $this->renderElement($elementName, array('answer'=>$answer, 'page'=>$page,'currentAnswerID'=>$currentAnswerID));
  }
  print '</div>';
}?>
<div id='counter'><?php 
  if($page->getJazzeePage()->getAnswers()){
    $elementName = \Foundation\VC\Config::findElementCacading($page->getPage()->getType()->getClass(), '', '-counter');
    $this->renderElement($elementName, array('page'=>$page));
  }?>
</div>
<?php
if(!empty($currentAnswerID) or is_null($page->getMax()) or count($page->getJazzeePage()->getAnswers()) < $page->getMax()){
  $elementName = \Foundation\VC\Config::findElementCacading($page->getPage()->getType()->getClass(), '', '-form');
  $this->renderElement($elementName, array('page'=>$page));
}