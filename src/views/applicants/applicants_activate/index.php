<?php
/**
 * applicants_activate index view
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
if (count($applicants)) {
  ?>
  <table>
    <caption>Deactivated Applicants</caption>
    <thead>
      <tr>
        <?php
        if ($this->controller->checkIsAllowed('applicants_activate', 'activate')) {
          print '<th>Activate</th>';
        }
        ?>
        <?php
        if ($this->controller->checkIsAllowed('applicants_single')) {
          print '<th>View</th>';
        }
        ?>
        <th>Last Name</th><th>First Name</th><th>Last Update</th><th>Progress</th><th>Tags</th><th>Last Login</th><th>Account Created</th>
      </tr>
    </thead>
    <tbody>
        <?php foreach ($applicants as $applicant) { ?>
        <tr>
          <?php if ($this->controller->checkIsAllowed('applicants_activate', 'activate')) { ?>
            <td><a href='<?php print $this->path('applicants/activate/activate/' . $applicant->getId()); ?>' title='<?php print $applicant->getFirstName() . ' ' . $applicant->getLastName(); ?>'>Activate</a></td>
    <?php } ?>
          <?php if ($this->controller->checkIsAllowed('applicants_single')) { ?>
            <td><a href='<?php print $this->path('applicants/single/' . $applicant->getId()); ?>' title='<?php print $applicant->getFirstName() . ' ' . $applicant->getLastName(); ?>'>View</a></td>
    <?php } ?>
          <td><?php print $applicant->getLastName(); ?></td>
          <td><?php print $applicant->getFirstName(); ?></td>
          <td><?php print $applicant->getUpdatedAt()->format('m/d/y'); ?></td>
          <td><?php print $applicant->getPercentComplete() * 100; ?>%</td>
          <?php
          $tags = array();
          foreach ($applicant->getTags() as $tag) {
            $tags[] = $tag->getTitle();
          }
          if ($applicant->isLocked()) {
            $tags[] = 'Locked';
          }
          if ($applicant->hasPaid()) {
            $tags[] = 'Paid';
          }
          if ($applicant->getDecision() and $applicant->getDecision()->getAcceptOffer()) {
            $tags[] = 'Accepted';
          }
          if ($applicant->getDecision() and $applicant->getDecision()->getFinalAdmit()) {
            $tags[] = 'Admitted';
          }
          if ($applicant->getDecision() and $applicant->getDecision()->getDeclineOffer()) {
            $tags[] = 'Declined';
          }
          if ($applicant->getDecision() and $applicant->getDecision()->getFinalDeny()) {
            $tags[] = 'Denied';
          }
          asort($tags);
          $tags = implode(', ', $tags);
          ?>
          <td><?php print $tags; ?></td>
          <td><?php print $applicant->getLastLogin()?$applicant->getLastLogin()->format('m/d/y'):'never'; ?></td>
          <td><?php print $applicant->getCreatedAt()->format('m/d/y'); ?></td>
        </tr>
  <?php } //applicants
  ?>
    </tbody>
  </table>
  <?php
} else { //if applicants
  print '<p>There are no deactivated applicants in this cycle.</p>';
}