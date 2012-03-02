<?php 
/**
 * admin_login NoAuthentication view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 */
$form = $authenticationClass->getLoginForm();
$this->renderElement('form', array('form'=>$form)) ?>