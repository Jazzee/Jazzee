<?php 
/**
 * setup_decisionletters index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
?>
<fieldset>
  <legend>Admit Letter
  <?php if($this->controller->checkIsAllowed('setup_decisionletters', 'editAdmitLetter')){ ?>
    (<a href='<?php print $this->path('setup/decisionletters/editAdmitLetter')?>'>Edit</a>)
    <?php }?>
  </legend>
  <?php print $admitLetter;?>
</fieldset>

<fieldset>
  <legend>Deny Letter
  <?php if($this->controller->checkIsAllowed('setup_decisionletters', 'editDenyLetter')){ ?>
    (<a href='<?php print $this->path('setup/decisionletters/editDenyLetter')?>'>Edit</a>)
    <?php }?>
  </legend>
  <?php print $denyLetter;?>
</fieldset>