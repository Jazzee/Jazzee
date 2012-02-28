<?php
require __DIR__ . '/config.php';

$cli = new \Symfony\Component\Console\Application('Jazzee Command Line Interface', '2');
$cli->setCatchExceptions(true);
$helperSet = $cli->getHelperSet();
foreach ($helpers as $name => $helper) {
    $helperSet->set($helper, $name);
}

$cli->addCommands(array(
  new \Jazzee\Console\Install(),
  new \Jazzee\Console\Update(),
  new \Jazzee\Console\AddUser(),
  new \Jazzee\Console\CreateAdminRole(),
  new \Jazzee\Console\UserRole(),
  new \Jazzee\Console\Preflight(),

));
$cli->run();
