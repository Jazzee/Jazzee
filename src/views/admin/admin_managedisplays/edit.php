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