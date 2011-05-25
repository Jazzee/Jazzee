<?php 
/**
 * applicants_list index view
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
foreach($tags as $title => $applicants):?>
<table>
<caption><?php print $title ?> (<?php print count($applicants); ?>)</caption>
  <thead>
    <tr><th>View</th><th>Last Name</th><th>First Name</th><th>Last Update</th><th>Last Login</th><th>Account Created</th></tr>
  </thead>
  <tbody>
    <?foreach($applicants as $applicant):?>
      <tr>
        <td><a href='<?php print $this->path("applicants/single/byId/{$applicant->id}")?>' title='<?php print "{$applicant->firstName} {$applicant->lastName}"?>'>Application</a></td>
        <td><?php print $applicant->lastName; ?></td>
        <td><?php print $applicant->firstName; ?></td>
        <td><?php print $applicant->updatedAt; ?></td>
        <td><?php print $applicant->lastLogin; ?></td>
        <td><?php print $applicant->createdAt; ?></td>
      </tr>
    <?php endforeach;?>
  </tbody>
</table>
<?php 
endforeach; //tags ?>
