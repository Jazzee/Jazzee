<?php 
/**
 * apply_page view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
?>
<p class='deadline<?php if($page->getApplication()->getClose()->diff(new DateTime('today'))->days < 7){ print ' approaching-deadline';}  ?>'>Application Deadline: <?php print $page->getApplication()->getClose()->format('m/d/Y g:ia T');?></p>
  <?php if($page->getJazzeePage()->getStatus() == \Jazzee\Page::SKIPPED){?>
    <p class="skip">You have selected to skip this page.  You can still change your mind and <a href='<?php print $this->controller->getActionPath() . '/do/unskip';?>' title='complete this page'>Complete This Page</a> if you wish.</p>
  <?php } else {
    if(!$page->isRequired() and !count($page->getJazzeePage()->getAnswers())){?>
      <p class="skip">This page is optional, if you do not have any information to enter you can <a href='<?php print $this->controller->getActionPath() . '/do/skip';?>' title='skip this page'>Skip This Page</a>.</p>
    <?php }
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
}