<?php 
/**
 * applicants_search index view
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
$this->renderElement('form', array('form'=>$form));
if(isset($applicants)){?>
  <h4>Search Results</h4>
  <table>
  <caption>Your search returned (<?php print count($applicants); ?>) results</caption>
    <thead>
      <tr><th>View</th><th>Last Name</th><th>First Name</th><th>Program</th><th>Last Update</th><th>Last Login</th><th>Account Created</th></tr>
    </thead>
    <tbody>
      <?php foreach($applicants as $applicant){?>
        <tr>
          <td><a class='applicantLink' programId='<?php print $applicant->getApplication()->getProgram()->getId(); ?>' href='<?php print $this->path('applicants/single/' . $applicant->getId());?>' title='<?php print $applicant->getFirstName() . ' ' . $applicant->getLastName();?>'>Application</a></td>
          <td><?php print $applicant->getLastName(); ?></td>
          <td><?php print $applicant->getFirstName(); ?></td>
          <td><?php print $applicant->getApplication()->getProgram()->getName(); ?></td>
          <td><?php print $applicant->getUpdatedAt()->format('m/d/y'); ?></td>
          <td><?php print $applicant->getLastLogin()->format('m/d/y'); ?></td>
          <td><?php print $applicant->getCreatedAt()->format('m/d/y'); ?></td>
        </tr>
      <?php } //applicants?>
    </tbody>
  </table>
  <?php 
} //if applicants