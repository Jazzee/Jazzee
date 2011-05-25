<?php 
/**
 * Display user messages
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
 
$messages = Message::getInstance();
while($m = $messages->read()){
  print "<p class='{$m['type']}'>{$m['message']}</p>";
}
?>