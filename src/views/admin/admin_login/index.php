<?php

/**
 * Admin login view
 */
$class = $this->controller->getConfig()->getAdminAuthenticationClass();
$this->renderElement($class::LOGIN_ELEMENT, array('authenticationClass' => $authenticationClass));