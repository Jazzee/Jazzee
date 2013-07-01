<?php
/**
 * manage_schools new view
 *
 */
?>
<a href="<?php print $this->path('manage/schools');?>">Return to School Search</a>
<?php
if (isset($form)) {
  $this->renderElement('form', array('form' => $form));
}