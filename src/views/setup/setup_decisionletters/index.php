<?php
/**
 * setup_decisionletters index view
 *
 */
?>
<fieldset>
  <legend>Admit Letter<?php
  if ($this->controller->checkIsAllowed('setup_decisionletters', 'editAdmitLetter')) { ?>
    (<a href='<?php print $this->path('setup/decisionletters/editAdmitLetter')?>'>Edit</a>)<?php
  }?>
  </legend>
  <?php print $admitLetter;?>
</fieldset>

<fieldset>
  <legend>Deny Letter<?php
  if ($this->controller->checkIsAllowed('setup_decisionletters', 'editDenyLetter')) { ?>
    (<a href='<?php print $this->path('setup/decisionletters/editDenyLetter')?>'>Edit</a>)<?php
  }?>
  </legend>
  <?php print $denyLetter;?>
</fieldset>