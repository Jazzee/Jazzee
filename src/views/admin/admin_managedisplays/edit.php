<?php

/**
 * admin_managedisplays index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 */
?>
<a href="<?php print $this->path('managedisplays');?>">Return to display list</a>
<div>
  <form id='displayManager' method="post" action="<?print $this->path('managedisplays/edit/'.$display->getId());?>">
    <input id='displayName' name='displayName' type='text' value='<?php print $display->getName();?>'/>
    <div id='account'>
      <h5>Applicant Info</h5>
      <ol>
        <li>
          <input id='showFirstName' value='showFirstName' type='checkbox' name='account[]' <?php if($display->isFirstNameDisplayed()){ print 'checked="true" '; } ?>/>
          <label for='showFirstName'>First Name</label>
        </li>
        <li>
          <input id='showLastName' value='showLastName' type='checkbox' name='account[]' <?php if($display->isLastNameDisplayed()){ print 'checked="true" '; } ?>/>
          <label for='showLastName'>Last Name</label>
        </li>
        <li>
          <input id='showEmail' value='showEmail' type='checkbox' name='account[]' <?php if($display->isEmailDisplayed()){ print 'checked="true" '; } ?>/>
          <label for='showEmail'>Email</label>
        </li>
        <li>
          <input id='showLastLogin' value='showLastLogin' type='checkbox' name='account[]' <?php if($display->isLastLoginDisplayed()){ print 'checked="true" '; } ?>/>
          <label for='showLastLogin'>Last Login</label>
        </li>
        <li>
          <input id='showUpdatedAt' value='showUpdatedAt' type='checkbox' name='account[]' <?php if($display->isUpdatedAtDisplayed()){ print 'checked="true" '; } ?>/>
          <label for='showUpdatedAt'>Last Update</label>
        </li>
        <li>
          <input id='showCreatedAt' value='showCreatedAt' type='checkbox' name='account[]' <?php if($display->isCreatedAtDisplayed()){ print 'checked="true" '; } ?>/>
          <label for='showCreatedAt'>Account Created</label>
        </li>
        <li>
          <input id='showPercentComplete' value='showPercentComplete' type='checkbox' name='account[]' <?php if($display->isPercentCompleteDisplayed()){ print 'checked="true" '; } ?>/>
          <label for='showPercentComplete'>Percent Complete</label>
        </li>
        <li>
          <input id='showHasPaid' value='showHasPaid' type='checkbox' name='account[]' <?php if($display->isHasPaidDisplayed()){ print 'checked="true" '; } ?>/>
          <label for='showHasPaid'>Payment Status</label>
        </li>

      </ol>
    </div>
    <div id='pages'>
      <h5>Display Pages</h5>
      <ol>
      <?php foreach($applicationPages as $applicationPage){?>
        <li>
          <input id='page<?php print $applicationPage->getPage()->getId() ?>' value='<?php print $applicationPage->getPage()->getId() ?>' type='checkbox' name='pages[]' <?php if(in_array($applicationPage->getPage()->getId(), $display->getPageIds())){ print 'checked="true" '; } ?>/>
          <label for='page<?php print $applicationPage->getPage()->getId()?>'><?php print $applicationPage->getTitle()?></label>
          <div class='elements'>
            <ol>
              <?php foreach($applicationPage->getPage()->getElements() as $element){?>
              <li>
                <input id='element<?php print $element->getid() ?>' value='<?php print $element->getId() ?>' type='checkbox' name='page<?php print $applicationPage->getPage()->getId();?>elements[]' <?php if(in_array($element->getId(), $display->getElementIds())){ print 'checked="true" '; } ?> />
                <label for='element<?php print $element->getId()?>'><?php print $element->getTitle()?></label>
              </li>
              <?php } ?>
            </ol>
          </div>
        </li>
      <?php } ?>
      </ol>
    </div>
    <input type='submit' value='Save' />
  </form>
</div>