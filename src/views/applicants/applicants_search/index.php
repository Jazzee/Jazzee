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
  <table>
  <caption>Your search returned (<?php print count($applicants); ?>) results</caption>
    <thead>
      <tr><th>View</th><th>Last Name</th><th>First Name</th><th>Last Update</th><th>Last Login</th><th>Account Created</th></tr>
    </thead>
    <tbody>
      <?foreach($applicants as $applicant):?>
        <tr>
          <td><a href='<?php print $this->path('applicants/single/byId/' . $applicant->getId())?>' title='<?php print $applicant->getFirstName() . ' ' . $applicant->getLastName()?>'>Application</a></td>
          <td><?php print $applicant->getLastName(); ?></td>
          <td><?php print $applicant->getFirstName(); ?></td>
          <td><?php if($applicant->getUpdatedAt()) print $applicant->getUpdatedAt()->format('m/d/y'); ?></td>
          <td><?php if($applicant->getLastLogin()) print $applicant->getLastLogin()->format('m/d/y'); ?></td>
          <td><?php if($applicant->getCreatedAt()) print $applicant->getCreatedAt()->format('m/d/y'); ?></td>
        </tr>
      <?php endforeach;?>
    </tbody>
  </table>
<?php endif;?>