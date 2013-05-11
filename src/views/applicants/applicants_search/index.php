<?php
/**
 * applicants_search index view
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
$this->renderElement('form', array('form' => $form));
?>
<?php
  if ($this->controller->checkIsAllowed('applicants_search', 'advanced')) { ?>
    <p><a href='<?php print $this->path('applicants/search/advanced'); ?>'>Advanced Search</a>
<?php
  } ?>
<?php
  if (isset($applicants)) { ?>
  <h4>Search Results</h4>
  <table>
    <caption>Your search returned (<?php print count($applicants); ?>) results</caption>
    <thead>
      <tr><th>View</th><th>Name</th><th>Program</th><th>Last Update</th><th>Progress</th><th>Tags</th><th>Last Login</th><th>Account Created</th></tr>
    </thead>
    <tbody>
      <?php
        foreach ($applicants as $applicant) { ?>
          <tr>
            <td><a class='applicantLink' programId='<?php print $applicant->getApplication()->getProgram()->getId(); ?>' href='<?php print $this->path('applicants/single/' . $applicant->getId()); ?>' title='<?php print $applicant->getFirstName() . ' ' . $applicant->getLastName(); ?>'>Application</a></td>
            <td><?php print $applicant->getFullName(); ?></td>
            <td><?php print $applicant->getApplication()->getProgram()->getName(); ?></td>
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
            <td><?php print $applicant->getLastLogin()?$applicant->getLastLogin()->format('m/d/y'):'never'; ?></td>
            <td><?php print $applicant->getCreatedAt()->format('m/d/y'); ?></td>
          </tr>
      <?php
        } //applicants?>
    </tbody>
  </table>
<?php
  } //if applicants