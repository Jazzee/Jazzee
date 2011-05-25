<?php 
/**
 * applicants_search index view
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
      <?php if($this->controller->checkIsAllowed('applicants_single', 'index')): ?>
        <li><a href='<?php print $this->path("applicants/single/byId/{$applicant->id}")?>'><?php print "{$applicant->firstName} {$applicant->lastName}"?></li>
      <?php endif;?>
      
    <?php endforeach;?>
  </ol>
<?php endif; //applicants ?>
<?php if($this->controller->checkIsAllowed('applicants_list', 'index')): ?>
  <p><a href='<?php print $this->path('applicants/list/') ?>'>All Applicants</a></p>
<?php endif;?>
