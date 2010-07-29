<?php 
/**
 * List of programs
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
?>
<fieldset><legend>Select the program you are applying to:</legend><ul class='no-bullets'>
<?php foreach($programList as $program){
	if($program['visibleSum'] > 0) {
    echo '<li>';
    
    if($program['liveSum'] > 0) {
      print '<a href="' . $this->path("apply/{$program['shortName']}") . '/">' . $program['name'] . '</a>';
    } else {
      print $program['name'] . " Application <strong>(Closed)</strong>";
    }

    print '</li>';
	}
}
?>
</ul></fieldset>
