<?php
/**
 * applicants_create index view
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
if (isset($applicant)) { ?>
  <h4>Created Applicant</h4>
  <table>
    <thead>
      <tr><th>View</th><th>Name</th><th>Email</th><th>Password</th></tr>
    </thead>
        <tr>
          <td><a class='applicantLink' programId='<?php print $applicant->getApplication()->getProgram()->getId(); ?>' href='<?php print $this->path('applicants/single/' . $applicant->getId()); ?>' title='<?php print $applicant->getFirstName() . ' ' . $applicant->getLastName(); ?>'>Application</a></td>
          <td><?php print $applicant->getFullName(); ?></td>
          <td><?php print $applicant->getEmail(); ?></td>
          <?php if(isset($plainTextPassword)) { ?>
            <td><?php print $plainTextPassword; ?></td>
          <?php } else { ?>
            <td>encrypted</td>
          <?php } ?>
        </tr>
    </tbody>
  </table>
<?php  } //if applicants
$this->renderElement('form', array('form' => $form));
?>
<a href='<?php print $this->path('applicants/create/bulk'); ?>'>Upload Bulk applicant file</a>