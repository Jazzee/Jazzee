<?php
/**
 * applicants_decisions index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
?>
<fieldset>
  <legend>No Decision</legend>
  <?php
    if (empty($list['noDecision'])) {
  ?>
    <p>There are no applicants awaiting a decision.</p>
  <?php
    } else {
  ?>
    <?php
      if ($this->controller->checkIsAllowed('applicants_decisions', 'nominateAdmit')) { ?>
        <p><a href='<?php print $this->path('applicants/decisions/nominateAdmit'); ?>'>Nominate applicants for admission</a></p>
    <?php
      }
    ?>
    <?php
      if ($this->controller->checkIsAllowed('applicants_decisions', 'nominateDeny')) { ?>
        <p><a href='<?php print $this->path('applicants/decisions/nominateDeny'); ?>'>Nominate applicants for deny</a></p>
    <?php
      }
    ?>
    <ul>
      <?php
        foreach ($list['noDecision'] as $applicant) {
      ?>
        <li><?php print $applicant->getLastName() . ', ' . $applicant->getFirstName() . ' ' . $applicant->getMiddleName() ?></li>
      <?php
        }
      ?>
    </ul>
  <?php
    }
  ?>
</fieldset>

<fieldset>
  <legend>Nominated for Admission</legend>
  <?php
    if (empty($list['nominateAdmit'])) { ?>
      <p>There are no applicants nominated for admission.</p>
  <?php
    } else {
  ?>
    <?php
      if ($this->controller->checkIsAllowed('applicants_decisions', 'finalAdmit')) { ?>
        <p><a href='<?php print $this->path('applicants/decisions/finalAdmit'); ?>'>Admit Applicants</a></p>
    <?php
      }
    ?>
    <ul>
      <?php
        foreach ($list['nominateAdmit'] as $applicant) { ?>
        <li><?php print $applicant->getLastName() . ', ' . $applicant->getFirstName() . ' ' . $applicant->getMiddleName() ?></li>
      <?php
        }
      ?>
    </ul>
  <?php
    }
  ?>
</fieldset>

<fieldset>
  <legend>Nominated for Deny</legend>
  <?php
    if (empty($list['nominateDeny'])) {
  ?>
    <p>There are no applicants nominated for deny.</p>
  <?php
    } else {
  ?>
    <?php
      if ($this->controller->checkIsAllowed('applicants_decisions', 'finalDeny')) {
    ?>
      <p><a href='<?php print $this->path('applicants/decisions/finalDeny'); ?>'>Deny Applicants</a></p>
    <?php
      }
    ?>
    <ul>
      <?php
        foreach ($list['nominateDeny'] as $applicant) { ?>
          <li><?php print $applicant->getLastName() . ', ' . $applicant->getFirstName() . ' ' . $applicant->getMiddleName() ?></li>
      <?php
        }
      ?>
    </ul>
  <?php
    }
  ?>
</fieldset>

<fieldset>
  <legend>Admitted</legend>
  <?php
    if (empty($list['finalAdmit'])) { ?>
      <p>There are no admitted applicants who have not completed the SIR.</p>
  <?php
    } else {
  ?>
    <?php
      if ($this->controller->checkIsAllowed('applicants_decisions', 'acceptOffer')) { ?>
        <p><a href='<?php print $this->path('applicants/decisions/acceptOffer'); ?>'>Accept Applicants</a></p>
    <?php
      }
    ?>
    <?php
      if ($this->controller->checkIsAllowed('applicants_decisions', 'declineOffer')) { ?>
        <p><a href='<?php print $this->path('applicants/decisions/declineOffer'); ?>'>Decline Applicants</a></p>
    <?php
      }
    ?>
    <ul>
      <?php
        foreach ($list['finalAdmit'] as $applicant) { ?>
          <li><?php print $applicant->getLastName() . ', ' . $applicant->getFirstName() . ' ' . $applicant->getMiddleName() ?></li>
      <?php
        }
      ?>
    </ul>
  <?php
    }
  ?>
</fieldset>

<fieldset>
  <legend>Denied</legend>
  <?php
    if (empty($list['finalDeny'])) { ?>
      <p>There are no denied applicants.</p>
  <?php
    } else {
  ?>
    <ul>
      <?php
        foreach ($list['finalDeny'] as $applicant) { ?>
          <li><?php print $applicant->getLastName() . ', ' . $applicant->getFirstName() . ' ' . $applicant->getMiddleName() ?></li>
      <?php
        }
      ?>
    </ul>
  <?php
    }
  ?>
</fieldset>

<fieldset>
  <legend>Accepted Offer</legend>
  <?php
    if (empty($list['acceptOffer'])) { ?>
      <p>There are no admitted applicants who have accepted.</p>
  <?php
    } else {
  ?>
    <ul>
      <?php
        foreach ($list['acceptOffer'] as $applicant) { ?>
          <li><?php print $applicant->getLastName() . ', ' . $applicant->getFirstName() . ' ' . $applicant->getMiddleName() ?></li>
      <?php
        }
      ?>
    </ul>
  <?php
    }
  ?>
</fieldset>

<fieldset>
  <legend>Declined Offer</legend>
  <?php
    if (empty($list['declineOffer'])) { ?>
      <p>There are no admitted applicants who have declined.</p>
  <?php
    } else {
  ?>
    <ul>
      <?php
        foreach ($list['declineOffer'] as $applicant) { ?>
          <li><?php print $applicant->getLastName() . ', ' . $applicant->getFirstName() . ' ' . $applicant->getMiddleName() ?></li>
      <?php
        }
      ?>
    </ul>
  <?php
    }
  ?>
</fieldset>