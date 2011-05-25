<?php
/**
 * JSON layout
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */

$messages = array();
$mClass = Message::getInstance();
while($m = $mClass->read()){
  $messages[] = array('type'=>$m['type'], 'text'=>$m['message']);
}
//form uploads with files require a text area to wrap their response
if(isset($textarea) and $textarea):?>
<textarea>
{
  "status":<?php print json_encode($status); ?>,
  "messages":<?php print json_encode($messages); ?>,
  "messages":<?php print json_encode($messages); ?>,
  "data":{<?php print $layoutContent ?>}
}
</textarea>
<?php else: 
header("Content-type: application/json");
?>
{
  "status":<?php print json_encode($status); ?>,
  "messages":<?php print json_encode($messages); ?>,
  "data":{<?php print $layoutContent ?>}
}
<?php endif; ?>