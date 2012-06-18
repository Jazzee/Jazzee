<?php 
/**
 * apply_status view
 * @package jazzee
 * @subpackage apply
 */
$layoutContentTop = '<p class="links"><a href="' . $this->path('apply/' . $applicant->getApplication()->getProgram()->getShortName() . '/' . $applicant->getApplication()->getCycle()->getName() . '/support') . '">Support</a>';
if($count = $applicant->unreadMessageCount()) $layoutContentTop .= '<sup class="count">' . $count . '</sup>';
$layoutContentTop .= '<a href="' . $this->path('apply/' . $applicant->getApplication()->getProgram()->getShortName() . '/' . $applicant->getApplication()->getCycle()->getName() . '/applicant/logout') . '">Log Out</a></p>';
$this->controller->setLayoutVar('layoutContentTop', $layoutContentTop);

?>
<div id='statusPageText'>
  <?php print $statusPageText;?>
</div>
<?php 
if($applicant->isLocked()){
  foreach($pages as $page){
    if($page->answerStatusDisplay()){
      $class = $page->getPage()->getType()->getClass();
      $this->renderElement($class::applyStatusElement(), array('page'=>$page));
    }
  }
}