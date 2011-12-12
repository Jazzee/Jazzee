<?php
/**
 * Blank layout
 * Doesn't layout anythig, jut prints the view content
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
header('Content-Type:text/html; charset=UTF-8');
header('X-FRAME-OPTIONS: SAMEORIGIN');
print $layoutContent;
?>