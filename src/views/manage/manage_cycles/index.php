<?php
/**
 * manage_cycles index view
 *
 */
if ($cycles) {?>
  <h5>Current Cycles:</h5>
  <ul><?php
    foreach ($cycles as $cycle) { ?>
      <li><?php print $cycle->getName();
        if ($this->controller->checkIsAllowed('manage_cycles', 'edit')) { ?>
          (<a href='<?php print $this->path('manage/cycles/edit/') . $cycle->getId() ?>'>Edit</a>)<?php
        }?>
          <br /><strong>Required Pages:</strong>
          <ul>
        <?php foreach($cycle->getRequiredPages() as $requiredPage){?>
            <li><?php print $requiredPage->getTitle(); ?></li>
        <?php } ?>
          </ul>
      </li><?php
    }?>
  </ul><?php
}
if ($this->controller->checkIsAllowed('manage_cycles', 'new')) { ?>
  <p><a href='<?php print $this->path('manage/cycles/new') ?>'>Add a New Cycle</a></p><?php
}
