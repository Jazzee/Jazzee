<?php 
/**
 * apply_status view
 * @package jazzee
 * @subpackage apply
 */
if(!$applicant->isLocked()){?>
  <h2>Application Status: <em>Not Complete</em></h2>
  <p>Your application was not completed before the application deadline and will not be reviewed.</p>
<?php } else {?>
  <?php 
  switch($applicant->getDecision()->status()){
    case 'finalDeny': print '<h2>Application Status:<em>Denied</em></h2>'; break;
    case 'finalAdmit': print '<h2>Application Status:<em>Admitted</em></h2>'; break;
    case 'acceptOffer': print '<h2>Application Status:<em>Accepted</em></h2>'; break;
    case 'finalDeny': print '<h2>Application Status:<em>Declined</em></h2><p>You have declined our offer of admission or you have missed the deadline for enrollment confirmation.</p>'; break;
    default: print '<h2>Application Status:<em>Under Review</em></h2>';
  }?>
  <div id='statusPageText'><?php print $applicant->getApplication()->getStatusPageText();?></div>
  <?php foreach($pages as $page){
    $elementName = \Foundation\VC\Config::findElementCacading($page->getPage()->getType()->getClass(), '', '-status-page');
    $this->renderElement($elementName, array('page'=>$page));
  }
}
?>