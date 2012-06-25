<?php
/**
 * applicants_duplicates index view
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
?>
<h4>Possible Duplicate Applications</h4>
<table>
  <thead>
    <tr><th>View</th><th>Name</th><th>Last Update</th><th>Progress</th><th>Tags</th><th>Possible Duplicates</th></tr>
  </thead>
  <tbody>
    <?php
      foreach ($applicants as $applicant) { ?>
        <tr>
          <td><a class='applicantLink' programId='<?php print $applicant->getApplication()->getProgram()->getId(); ?>' href='<?php print $this->path('applicants/single/' . $applicant->getId()); ?>' title='<?php print $applicant->getFullName(); ?>'>Application</a></td>
          <td><?php print $applicant->getFullName(); ?></td>
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
          <td>
            <?php
            foreach ($applicant->getDuplicates() as $duplicate) {
              print $duplicate->getDuplicate()->getFullName() . ' ' . $duplicate->getDuplicate()->getPercentComplete() * 100 . '% ' . $duplicate->getDuplicate()->getApplication()->getProgram()->getName() . '<br />';
            }
            ?>
          </td>
        </tr>
    <?php
      } //applicants
    ?>
  </tbody>
</table>

<h4>Ignored Duplicate Applications</h4>
<table>
  <thead>
    <tr><th>View</th><th>Name</th><th>Last Update</th><th>Progress</th><th>Tags</th><th>Possible Duplicates</th></tr>
  </thead>
  <tbody>
    <?php
      foreach ($applicantsIgnored as $applicant) { ?>
        <tr>
          <td><a class='applicantLink' programId='<?php print $applicant->getApplication()->getProgram()->getId(); ?>' href='<?php print $this->path('applicants/single/' . $applicant->getId()); ?>' title='<?php print $applicant->getFullName(); ?>'>Application</a></td>
          <td><?php print $applicant->getFullName(); ?></td>
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
          <td>
            <?php
            foreach ($applicant->getDuplicates() as $duplicate) {
              print $duplicate->getDuplicate()->getFullName() . ' ' . $duplicate->getDuplicate()->getPercentComplete() * 100 . '% ' . $duplicate->getDuplicate()->getApplication()->getProgram()->getName() . '<br />';
            }
            ?>
          </td>
        </tr>
    <?php
      } //applicants
    ?>
  </tbody>
</table>