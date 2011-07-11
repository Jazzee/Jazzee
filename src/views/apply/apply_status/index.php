<?php 
/**
 * apply_status view
 * @package jazzee
 * @subpackage apply
 */
?>
<div id='statusPageText'>
  <?php print $statusPageText;?>
</div>
<?php 
if($applicant->isLocked()){
  foreach($pages as $page){
    $elementName = \Foundation\VC\Config::findElementCacading($page->getPage()->getType()->getClass(), '', '-status-page');
    $this->renderElement($elementName, array('page'=>$page));
  }
}