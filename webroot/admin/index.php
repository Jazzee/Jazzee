<?php
/**
 * This index file should be place in your servers webroot.  
 * It is the only file in jazzee that shoud be publically availalble.
 * 
 * by modifiy the paths for the bootstrap and routes files you can put the jazzee source wherever you want
 */

//include the jazzee bootstrap file
require_once(__DIR__ . '/../../src/jazzee-bootstrap.php');

//route requests
require_once(__DIR__ . '/../../src/routes-admin.php');
?>
