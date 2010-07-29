<?php 
/**
 * applicants_view list view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
?>
<?php if(isset($applicants)):?>
  <h3>Applicants</h3>
  <ol>
    <?foreach($applicants as $applicant):?>
      <li><a href='<?php print $this->path("applicants/view/single/{$applicant->id}")?>'><?php print "{$applicant->firstName} {$applicant->lastName}"?></li>
    <?php endforeach;?>
  </ol>
<?php endif; ?>