<?php 
/**
 * manage_scores index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage manage
 */
if(isset($form)){
  $this->renderElement('form', array('form'=>$form));
}
?>
<table><caption>Score Statistics</caption>
<thead><tr><th>Score Type</th><th>Total in the system</th><th>Matched to applicant</th><th>Entered by applicants and not in the system</th></tr></thead>
<tbody>
<tr><td>GRE</td><td><?php print $greCount ?></td><td><?php print $greMatchedCount ?></td><td><?php print $greUnmatchedCount ?></td></tr>
<tr><td>TOEFL</td><td><?php print $toeflCount ?></td><td><?php print $toeflMatchedCount ?></td><td><?php print $toeflUnmatchedCount ?></td></tr>
</tbody>
</table>