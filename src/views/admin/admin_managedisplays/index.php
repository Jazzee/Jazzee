<?php
/**
 * admin_managedisplays index view
 *
 */
if ($displays) {?>
  <h5>Your Displays:</h5>
  <ul><?php
    foreach ($displays as $display) { ?>
      <li><?php print $display['name'];?>
        (<a href='<?php print $this->path('managedisplays/edit/') . $display['id'] ?>'>Edit</a>)
        (<a href='<?php print $this->path('managedisplays/delete/') . $display['id'] ?>'>Delete</a>)
      </li><?php
    }?>
  </ul><?php
}?>
<p><a href='<?php print $this->path('managedisplays/new') ?>'>Add a New Display</a></p><?php
