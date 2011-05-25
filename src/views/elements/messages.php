<?php 
/**
 * Display user messages
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
$messages = $this->controller->getMessages();
foreach($messages as $message){
  print "<p class='{$message['type']}'>{$message['text']}</p>";
}
?>