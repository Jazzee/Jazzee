<?php
/**
 * Setup Tests
 * 
 */
error_reporting(E_ALL);

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('Jazzee', __DIR__ . '/src');

require __DIR__ . '/../vendor/cordoval/hamcrest-php/hamcrest/Hamcrest.php';