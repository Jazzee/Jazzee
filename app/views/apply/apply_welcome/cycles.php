<?php 
/**
 * List all the cycles for a program
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
?>
<fieldset><legend>Select the cycle you are applying to:</legend><ul>
<?php
foreach($program->Applications AS $application){
  if($application->visible){
    echo '<li>';
    if($application->published){
      if($application->open AND strtotime($application->open) < time()){
        echo '<a href="' . $this->path("apply/{$application->Program->shortName}/{$application->Cycle->name}") . '">' . $application->Cycle->name . ' Application</a>';
        if(strtotime($application->close) < time()){
          echo ' <strong>(Closed)</strong>';
        }
      } else {
        echo $application->Cycle->name . ' Application <strong>(Closed)</strong';
      }
      echo '<br />Classes Begin: ';
      $this->renderElement('long_date', array('date'=>$application->begin));
      echo '<br />Application Available: ';
      $this->renderElement('long_date', array('date'=>$application->open));
      echo ' - ';
      $this->renderElement('long_date', array('date'=>$application->close));
    } else {
      echo $application->Cycle->name . ' Application <strong>(Closed)</strong';
      echo '<br />Classes Begin: ';
      $this->renderElement('long_date', array('date'=>$application->begin));
      echo '<br />Application Available: ';
      if($application->open AND $application->close){
        $this->renderElement('long_date', array('date'=>$application->open));
        echo ' &#45; ';
        $this->renderElement('long_date', array('date'=>$application->close));
        print ' (These dates are not final)';
      } else {
        echo 'Application dates have not been determined';
      }
    }
    echo '</li><hr />';
  }
}
?>
</ul></fieldset>  