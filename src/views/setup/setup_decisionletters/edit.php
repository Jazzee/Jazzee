<?php

/**
 * setup_decisionletters edit view
 *
 */
?>
<p><a href='<?php print $this->path('setup/decisionletters') ?>'>Return to template list</a></p>
<?php if(isset($template)){?>
    <fieldset>
      <legend>Current <?php $template->getTitle();?></legend>
      <?php print nl2br($template->renderText($search, $replace));?>
    </fieldset>
<?php }
if (isset($form)) {
  $this->renderElement('form', array('form' => $form));
}