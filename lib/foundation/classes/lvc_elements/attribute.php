<?php
/**
 * HTML attributes view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 */
if(isset($value)){
  echo " {$name}=\"" . htmlentities($value) . '"';
}
?>