<?php
/**
 * Initial Install
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
?>
<h2>Install Jazzee</h2>
<?php
if(!empty($messages)){
  foreach($messages as $message){
    print "<p>{$message}</p>";
  }
}
if(!empty($form)){
  $this->renderElement('form', array('form'=>$form));
}
?>