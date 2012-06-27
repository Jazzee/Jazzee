<?php
/**
 * Recommenders page LOR single recommender info element
 */
if ($this->controller->checkIsAllowed('applicants_single', 'editAnswer')) {
  if ($answer->getChildren()->first()) { ?>
    <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/do/editLor/' . $answer->getId()); ?>' class='actionForm'>Edit Recommendation</a><br /><?php
  } else { ?>
    <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/editAnswer/' . $answer->getId()); ?>' class='actionForm'>Edit Recommender Information</a><br />
    <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/do/sendAdminInvitation/' . $answer->getId()); ?>' class='actionForm'>Send Invitation</a><br />
    <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/do/completeLor/' . $answer->getId()); ?>' class='actionForm'>Complete Recommendation</a><br />
    <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/do/viewLink/' . $answer->getId()); ?>' class='actionForm'>View Link</a><br /><?php
  }
}
if ($this->controller->checkIsAllowed('applicants_single', 'deleteAnswer')) {
  if ($answer->getChildren()->first()) { ?>
    <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/doAction/deleteLor/' . $answer->getId()); ?>' class='action confirmDelete'>Delete Recommendation</a><br /><?php
  } else { ?>
    <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/deleteAnswer/' . $answer->getId()); ?>' class='action confirmDelete'>Delete</a><br /><?php
  }
}