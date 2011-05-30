<?php
/**
 * JSON layout
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */

//form uploads with files require a text area to wrap their response
if(isset($textarea) and $textarea):?>
<textarea>
{
  "status":<?php print json_encode($status); ?>,
  "messages":<?php print json_encode($this->controller->getMessages()); ?>,
  "data":{<?php print $layoutContent ?>}
}
</textarea>
<?php else: 
header("Content-type: application/json");
?>
{
  "status":<?php print json_encode($status); ?>,
  "messages":<?php print json_encode($this->controller->getMessages()); ?>,
  "data":{<?php print $layoutContent ?>}
}
<?php endif; ?>