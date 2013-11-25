<?php

/**
 * setup_decisionletters new view
 *
 */
?>
<p><a href='<?php print $this->path('setup/decisionletters') ?>'>Return to template list</a></p>
<?php
if (isset($form)) {
  $this->renderElement('form', array('form' => $form));
}