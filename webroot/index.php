<?php
/**
 * Index for applicants
 * 
 * It is the only publicly accessible file
 * Make sure the .htaccess file is properly redirecting all traffic here
 * 
 * by modifying the paths for the bootstrap and routes files you can put this file and the jazzee source wherever you want
 */

//include the jazzee bootstrap file
require_once(__DIR__ . '/../src/jazzee-bootstrap.php');

//route requests
require_once(__DIR__ . '/../src/routes.php');
?>
