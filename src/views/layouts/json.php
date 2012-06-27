<?php
/**
 * JSON layout
 *
 */
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
//form uploads with files require a text area to wrap their response
if (isset($textarea) and $textarea){?>
  <textarea>
  {
    "status":<?php print json_encode($status); ?>,
    "messages":<?php print json_encode($this->controller->getMessages()); ?>,
    "data":{<?php print $layoutContent ?>}
  }
  </textarea><?php
} else {
  header("Content-type: application/json");?>
  {
  "status":<?php print json_encode($status); ?>,
  "messages":<?php print json_encode($this->controller->getMessages()); ?>,
  "data":{<?php print $layoutContent ?>}
  }<?php
}?>