<?php 
/**
 * applicants_list index view
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
?>
<div id='selectors'></div>
<?php foreach($tags as $title => $applicants){?>
  <?php if(count($applicants)){?>
    <table id='<?php print strtolower(preg_replace('#[^a-z0-9]#i', '', $title)); ?>'>
    <caption><?php print $title; ?> (<?php print count($applicants); ?>)</caption>
      <thead>
        <tr><th>View</th><th>Last Name</th><th>First Name</th><th>Last Update</th><th>Progress</th><th>Last Login</th><th>Account Created</th></tr>
      </thead>
      <tbody>
        <?php foreach($applicants as $applicant){?>
          <tr>
            <td><a href='<?php print $this->path('applicants/single/' . $applicant->getId());?>' title='<?php print $applicant->getFirstName() . ' ' . $applicant->getLastName();?>'>Application</a></td>
            <td><?php print $applicant->getLastName(); ?></td>
            <td><?php print $applicant->getFirstName(); ?></td>
            <td><?php print $applicant->getUpdatedAt()->format('m/d/y'); ?></td>
            <td><?php print $applicant->getPercentComplete()*100; ?>%</td>
            <td><?php print $applicant->getLastLogin()->format('m/d/y'); ?></td>
            <td><?php print $applicant->getCreatedAt()->format('m/d/y'); ?></td>
          </tr>
        <?php } //applicants?>
      </tbody>
    </table>
    <?php 
  } //if applicants
} //tags ?>