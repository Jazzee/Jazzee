<?php
/**
 * Initial Install
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 */
?>
<h2>Setup Database</h2>
<?php if(isset($message)):?>
  <h4>Error</h4>
  <p><?php print $message ?></p>
<?php endif;?>
<?php if(isset($form)) $this->renderElement('form', array('form'=>$form)); ?>