<?php
/**
 * apply_page view
 */

if ($page->getJazzeePage()->getStatus() == \Jazzee\Interfaces\Page::SKIPPED) { ?>
  <p class="skip">You have selected to skip this page.  You can still change your mind and <a href='<?php print $this->controller->getActionPath() . '/do/unskip'; ?>' title='complete this page'>Complete This Page</a> if you wish.</p><?php
} else {
  if (!$page->isRequired() and (is_null($applicant->getExternalId()))) {?>
    <p class="skip">This page is optional, if you do not have any information to enter you can <a href='<?php print $this->controller->getActionPath() . '/do/skip'; ?>' title='skip this page'>Skip This Page</a>.</p><?php
  } ?>
   <?php
      if (!is_null($applicant->getExternalId())
	  && ($this->controller->getActionName() != 'edit')){
	?>
    <div id='answers'>
        <div class='answer active'>
          <h5>External ID</h5><?php
	  print '<p><strong>External ID :</strong>&nbsp;' . $applicant->getExternalId() . '</p>';
	?>
	  <p class='controls'>
              <a class='edit' href='<?php print $this->controller->getActionPath(); ?>/edit/0'>Edit</a>
              <a class='delete' href='<?php print $this->controller->getActionPath(); ?>/delete/0'>Delete</a>
          </p>
        </div>
       </div><?php
      } //end if !is_null(...)
    ?><?php
  }
?>
 <div id='leadingText'><?php print $page->getLeadingText() ?></div>

    <?php
  if (is_null($applicant->getExternalId()) || ($this->controller->getActionName() == 'edit')){
      $this->renderElement('form', array('form' => $page->getJazzeePage()->getForm()));
    }
 ?>
    <div id='trailingText'><?php print $page->getTrailingText() ?></div>
<?php

    