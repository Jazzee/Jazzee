<?php
/**
 * apply_page view
 */
if(!isset($edit)){
  $edit = false;
}
if ($page->getJazzeePage()->getStatus() == \Jazzee\Interfaces\Page::SKIPPED) { ?>
  <p class="skip">You have selected to skip this page.  You can still change your mind and <a href='<?php print $this->controller->getActionPath() . '/do/unskip'; ?>' title='complete this page'>Complete This Page</a> if you wish.</p><?php
} else {
  if (!$page->isRequired() and (is_null($applicant->getExternalId()))) {?>
    <p class="skip">This page is optional, if you do not have any information to enter you can <a href='<?php print $this->controller->getActionPath() . '/do/skip'; ?>' title='skip this page'>Skip This Page</a>.</p><?php
  } 
  if ($applicant->getExternalId()) {?>
    <div id='answers'>
      <div class='answer<?php 
        if($edit) {
          print ' active';
        }?>'>
        <h5>Saved Answer</h5><?php
	  print '<p><strong>' . $page->getPage()->getVar('externalIdLabel') . ':</strong>&nbsp' . $applicant->getExternalId() . '</p>';
	?>
	  <p class='controls'><?php
            if ($edit) { ?>
              <a class='undo' href='<?php print $this->controller->getActionPath() ?>'>Undo</a><?php
            } else { ?>
              <a class='edit' href='<?php print $this->controller->getActionPath(); ?>/edit/0'>Edit</a>
              <a class='delete' href='<?php print $this->controller->getActionPath(); ?>/delete/0'>Delete</a><?php
            } ?>
          </p>
      </div>
    </div><?php
  } //end if appilcant ahs externalId 
  ?>
  <div id='leadingText'><?php print $page->getLeadingText() ?></div>
  <?php
  if (is_null($applicant->getExternalId()) || $edit){
    $this->renderElement('form', array('form' => $page->getJazzeePage()->getForm()));
  }
  ?>
  <div id='trailingText'><?php print $page->getTrailingText() ?></div>
<?php } //end else skipped