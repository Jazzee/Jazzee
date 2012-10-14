<?php

/**
 * setup_tags view
 *
 */
?>
<h3>Application Tags</h3>
<ul>
  <?php foreach($tags as $tag) { ?>
  <li>
    <?php print $tag->getTitle();
    if ($this->controller->checkIsAllowed('setup_tags', 'edit')) { ?>
      (<a href='<?php print $this->path('setup/tags/edit/' . $tag->getId()) ?>'>Edit</a>)<?php
    }
    if ($this->controller->checkIsAllowed('setup_tags', 'remove')) { ?>
      (<a href='<?php print $this->path('setup/tags/remove/' . $tag->getId()) ?>'>Remove from all applicants</a>)<?php
    } ?>
  </li>
  <?php } ?>
</ul>