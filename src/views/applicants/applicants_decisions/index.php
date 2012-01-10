<?php 
/**
 * applicants_decisions index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
if(!empty($list['noDecision'])){?>
  <div>
    <h4>No Decision<h4>
      <?php if($this->controller->checkIsAllowed('applicants_decisions', 'nominateAdmit')){?>
        <p><a href='<?php print $this->path('applicants/decisions/nominateAdmit');?>'>Nominate applicants for admission</a></p>
      <?php }?>
      <?php if($this->controller->checkIsAllowed('applicants_decisions', 'nominateDeny')){?>
        <p><a href='<?php print $this->path('applicants/decisions/nominateDeny');?>'>Nominate applicants for deny</a></p>
      <?php }?>
    <ul>
     <?php foreach($list['noDecision'] as $applicant){?>
        <li><?php print $applicant->getLastName() . ', ' . $applicant->getFirstName() . ' ' . $applicant->getMiddleName() ?></li>
     <?php } ?>
    </ul>
  </div>
<?php } ?>

<?php if(!empty($list['nominateAdmit'])){?>
  <div>
    <h4>Nominated for Admission<h4>
      <?php if($this->controller->checkIsAllowed('applicants_decisions', 'finalAdmit')){?>
        <p><a href='<?php print $this->path('applicants/decisions/finalAdmit');?>'>Admit Applicants</a></p>
      <?php }?>
    <ul>
     <?php foreach($list['nominateAdmit'] as $applicant){?>
        <li><?php print $applicant->getLastName() . ', ' . $applicant->getFirstName() . ' ' . $applicant->getMiddleName() ?></li>
     <?php } ?>
    </ul>
  </div>
<?php } ?>

<?php if(!empty($list['nominateDeny'])){?>
  <div>
    <h4>Nominated for Deny<h4>
      <?php if($this->controller->checkIsAllowed('applicants_decisions', 'finalDeny')){?>
        <p><a href='<?php print $this->path('applicants/decisions/finalDeny');?>'>Deny Applicants</a></p>
      <?php }?>
    <ul>
     <?php foreach($list['nominateDeny'] as $applicant){?>
        <li><?php print $applicant->getLastName() . ', ' . $applicant->getFirstName() . ' ' . $applicant->getMiddleName() ?></li>
     <?php } ?>
    </ul>
  </div>
<?php } ?>