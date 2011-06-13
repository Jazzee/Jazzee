<?php
/**
 * Index for admin
 * 
 * It is the only public location available for administrators 
 * Make sure the .htaccess file is properly redirecting all admin traffic here
 * 
 * by modifying the paths for the bootstrap and routes files you can put this file and the jazzee source wherever you want
 */
//include the jazzee bootstrap file
require_once(__DIR__ . '/../../src/jazzee-bootstrap.php');

//route requests
require_once(__DIR__ . '/../../src/routes-admin.php');
?>
