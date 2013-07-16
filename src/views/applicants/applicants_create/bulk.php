<?php
/**
 * applicants_create index view
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
if (isset($results)) {  ?>
  <h4>Results</h4>
  <table>
  <thead>
    <tr><th>Status</th><th>View</th><th>Name</th><th>Email</th><th>Password</th><th>Messages</th></tr>
  </thead><?php
  foreach($results as $arr){
    switch($arr['status']){
        case 'success':
        case 'duplicate':
          $applicant = $arr['applicant'];?>
          <tr>
            <td><?php print $arr['status']; ?></td>
            <td><a class='applicantLink' programId='<?php print $applicant->getApplication()->getProgram()->getId(); ?>' href='<?php print $this->path('applicants/single/' . $applicant->getId()); ?>' title='<?php print $applicant->getFirstName() . ' ' . $applicant->getLastName(); ?>'>Application</a></td>
            <td><?php print $applicant->getFullName(); ?></td>
            <td><?php print $applicant->getEmail(); ?></td>
            <td><?php print $arr['plainTextPassword']; ?></td>
            <td>
              <?php foreach($arr['messages'] as $message){
                print $message . '<br />';
              }?>
            </td>
          </tr><?php
        break;
        case 'badExternalId':?>
          <tr>
            <td>Bad External ID</td>
            <td></td>
            <td><?php print $arr['applicantName']; ?></td>
            <td><?php print $arr['applicantEmail']; ?></td>
            <td><?php print $arr['plainTextPassword']; ?></td>
            <td>
              <?php foreach($arr['messages'] as $message){
                print $message . '<br />';
              }?>
            </td>
          </tr><?php
        break;  
      } //switch
  } //if results
}  //foreach applicants ?>

  </tbody>
</table><?php
$this->renderElement('form', array('form' => $form)); ?>
<a href='<?php print $this->path('applicants/create/sampleFile'); ?>'>Download Sample file</a>