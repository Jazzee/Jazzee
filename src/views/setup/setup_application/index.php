<?php
/**
 * setup_application index view
 *
 */
$dateFormat = 'l F jS Y g:ia';
?>
<fieldset>
  <legend>Contact Information<?php
    if ($this->controller->checkIsAllowed('setup_application', 'editContact')) { ?>
      (<a href='<?php print $this->path('setup/application/editContact') ?>'>Edit</a>)<?php
    } ?>
  </legend>
  Contact Name: <?php print $application->getContactName(); ?><br />
  Contact Email: <?php print $application->getContactEmail(); ?><br />
</fieldset>
<fieldset>
  <legend>Welcome Message<?php
    if ($this->controller->checkIsAllowed('setup_application', 'editWelcome')) { ?>
      (<a href='<?php print $this->path('setup/application/editWelcome') ?>'>Edit</a>)<?php
    } ?>
  </legend>
<?php print $application->getWelcome(); ?>
</fieldset>
<?php
$statusMessages = array(
  'StatusIncomplete' => 'Message for applicants who missed the deadline',
  'StatusDeactivated' => 'Message for applicants who have been deactivated',
  'StatusNoDecision' => 'Message for locked applicants with no decision',
  'StatusAdmit' => 'Message for admitted applicants',
  'StatusDeny' => 'Message for denied applicants',
  'StatusAccept' => 'Message for applicants who accept their offer',
  'StatusDecline' => 'Message for applicants who decline their offer'
);

$search = array(
  '_Applicant_Name_',
  '_Application_Deadline_',
  '_Offer_Response_Deadline_',
  '_SIR_Link_',
  '_Admit_Letter_',
  '_Deny_Letter_',
  '_Admit_Date_',
  '_Deny_Date_',
  '_Accept_Date_',
  '_Decline_Date_'
);
$someDate = new DateTime('midnight');
$replace = array(
  'John Doe Applicant',
  $application->getClose() ? $application->getClose()->format($dateFormat) : $someDate->format($dateFormat),
  $someDate->format($dateFormat),
  '#',
  '#',
  '#',
  $someDate->format($dateFormat),
  $someDate->format($dateFormat),
  $someDate->format($dateFormat),
  $someDate->format($dateFormat)
);
foreach ($statusMessages as $status => $title) {
  ?>
  <fieldset>
    <legend><?php print $title;
      if ($this->controller->checkIsAllowed('setup_application', 'edit' . $status)) { ?>
        (<a href='<?php print $this->path('setup/application/edit' . $status) ?>'>Edit</a>)<?php
      } ?>
    </legend>
  <?php
    $f = 'get' . $status . 'Text';
    print str_replace($search, $replace, $application->$f());
  ?>
  </fieldset><?php
} //end foreach status type  ?>
<fieldset>
  <legend>Status<?php
    if ($this->controller->checkIsAllowed('setup_application', 'editStatus')) { ?>
      (<a href='<?php print $this->path('setup/application/editStatus') ?>'>Edit</a>)<?php
    } ?>
  </legend>
  Application Opens: <?php print ($application->getOpen() ? $application->getOpen()->format($dateFormat) : 'not set'); ?><br />
  Application Closes: <?php print ($application->getClose() ? $application->getClose()->format($dateFormat) : 'not set'); ?><br />
  Program Begins: <?php print ($application->getBegin() ? $application->getBegin()->format($dateFormat) : 'not set'); ?><br />
  Visible: <?php print ($application->isVisible() ? 'Yes' : 'No'); ?><br />
  By Invitation Only: <?php print ($application->isByInvitationOnly() ? 'Yes' : 'No'); ?><br />
</fieldset>
<fieldset>
  <legend>External ID Validation<?php
    if ($this->controller->checkIsAllowed('setup_application', 'editExternalIdValidation')) { ?>
      (<a href='<?php print $this->path('setup/application/editExternalIdValidation') ?>'>Edit</a>)<?php
    } ?>
  </legend>
  External ID Validation: <?php print ($application->getExternalIdValidationExpression() ? 'Regular Expression: ' . $application->getExternalIdValidationExpression() : 'No'); ?><br />
</fieldset>