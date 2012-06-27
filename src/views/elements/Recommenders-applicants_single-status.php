<?php
/**
 * Recommenders page LOR single status element
 */
?>
<strong>Last Updated:</strong> <?php print $answer->getUpdatedAt()->format('M d Y g:i a'); ?><br /><?php
if ($child = $answer->getChildren()->first()) { ?>
  <br /><strong>Status:</strong> This recommendation was received on <?php print $child->getUpdatedAt()->format('l F jS Y g:ia');
} else if ($answer->isLocked()) {?>
  <strong>Invitation Sent:</strong> <?php print $answer->getUpdatedAt()->format('l F jS Y g:ia'); ?><br /><?php
}