<?php 
/**
 * List all the cycles for a program
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
/*
 * @var string format for dates this page
 */
$dateFormat = 'm/d/Y';
?>
<fieldset>
	<legend>Select the cycle you are applying to:</legend>
	<ul class='nobullets'>
  <?php
  foreach($applications AS $application){
    if($application->isVisible()){
      echo '<li>';
      if($application->isPublished()){
        if($application->getOpen() < new DateTime('now')){
          echo '<a href="' . $this->path('apply/' . $application->getProgram()->getShortName() . '/' . $application->getCycle()->getName()) . '">' . $application->getCycle()->getName() . ' Application</a>';
          if($application->getClose() < new DateTime('now')){
            echo ' <strong>(Closed)</strong>';
          }
        } else {
          echo $application->getCycle()->getName() . ' Application <strong>(Closed)</strong>';
        }
        echo '<br />Classes Begin: ' . $application->getBegin()->format($dateFormat);
        echo '<br />Application Available: ' . $application->getOpen()->format($dateFormat);
        echo ' - ' . $application->getClose()->format($dateFormat);
      } else {
        echo $application->getCycle()->getName() . ' Application <strong>(Closed)</strong>';
        echo '<br />Classes Begin: ' . $application->getBegin()->format($dateFormat);
        echo '<br />Application Available: ';
        if($application->getOpen() AND $application->getClose()){
          echo  $application->getOpen()->format($dateFormat) . ' &#45; ' . $application->getClose()->format($dateFormat);
          print ' (These dates are not final)';
        } else {
          echo 'Application dates have not been determined';
        }
      }
      echo '</li><hr />';
    }
  }
  ?>
	</ul>
</fieldset>  