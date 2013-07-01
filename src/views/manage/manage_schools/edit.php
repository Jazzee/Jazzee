<?php
/**
 * manage_schools edit view
 *
 */
?>
<a href="<?php print $this->path('manage/schools');?>">Return to School Search</a>
<?php
if (isset($form)) {
  $this->renderElement('form', array('form' => $form));
}