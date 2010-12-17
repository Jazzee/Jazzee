<?php 
/**
 * List of programs
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
?>
<fieldset><legend>Select the program you are applying to:</legend><ul>
<?php foreach($programs as $program){
	if($program->Applications->count() > 0) {
    foreach($program->Applications as $application){
      if($application->published AND $application->visible){
        print '<li>';
        print '<a href="' . $this->path("apply/{$program['shortName']}") . '/">' . $program['name'] . '</a>';
        print '</li>';
        break;
      }
    }
	}
}
?>
</ul></fieldset>
