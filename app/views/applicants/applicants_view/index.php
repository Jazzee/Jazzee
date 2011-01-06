<?php 
/**
 * applicants_view index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
$this->renderElement('form', array('form'=>$form));
if(isset($applicants)):?>
  <h4>Search Results</h4>
  <p>Your search returned <?php print count($applicants); ?> results</p>
  <ol>
    <?foreach($applicants as $applicant):?>
      <?php if($this->controller->checkIsAllowed('applicants_view', 'single')): ?>
        <li><a href='<?php print $this->path("applicants/view/single/{$applicant->id}")?>'><?php print "{$applicant->firstName} {$applicant->lastName}"?></li>
      <?php endif;?>
      
    <?php endforeach;?>
  </ol>
<?php endif; //applicants ?>
<?php if($this->controller->checkIsAllowed('applicants_view', 'list')): ?>
  <p><a href='<?php print $this->path('applicants/view/list') ?>'>All Applicants</a></p>
<?php endif;?>
