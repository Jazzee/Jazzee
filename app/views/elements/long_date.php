<?php 
/**
 * Display Dates in a nice form
 * Can be overridden by themes
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
if($date)
 echo date('m/d/Y', strtotime($date)) ?>