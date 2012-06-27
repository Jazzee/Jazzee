<?php

/**
 * Display user messages
 */
$messages = $this->controller->getMessages();
foreach ($messages as $message) {
  print "<p class='{$message['type']}'>{$message['text']}</p>";
}