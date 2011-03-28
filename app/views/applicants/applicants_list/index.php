<?php 
/**
 * applicants_list index view
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
if(isset($applicants)):?>
  <h4>Applicants</h4>
  <p>There are <?php print count($applicants); ?> applicants</p>
  <ol>
    <?foreach($applicants as $applicant):?>
      <?php if($this->controller->checkIsAllowed('applicants_single', 'index')): ?>
        <li><a href='<?php print $this->path("applicants/single/byId/{$applicant->id}")?>'><?php print "{$applicant->firstName} {$applicant->lastName}"?></a></li>
      <?php endif;?>
      
    <?php endforeach;?>
  </ol>
<?php endif; //applicants ?>
