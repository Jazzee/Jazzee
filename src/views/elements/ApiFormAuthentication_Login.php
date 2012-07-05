<?php

/**
 * admin_login NoAuthentication view
 * 
 */
$form = $authenticationClass->getLoginForm();
$this->renderElement('form', array('form' => $form));