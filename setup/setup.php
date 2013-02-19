<?php
require __DIR__ . '/config.php';

$cli = new \Symfony\Component\Console\Application('Jazzee Command Line Interface', '2');
$cli->setCatchExceptions(true);
$helperSet = $cli->getHelperSet();
foreach ($helpers as $name => $helper) {
    $helperSet->set($helper, $name);
}

$cli->addCommands(array(
  new \Jazzee\Console\Update(),
  new \Jazzee\Console\Defaults(),
  new \Jazzee\Console\AddUser(),
  new \Jazzee\Console\FindUser(),
  new \Jazzee\Console\UserRole(),
  new \Jazzee\Console\EveryoneRole(),
  new \Jazzee\Console\Preflight(),
  new \Jazzee\Console\MailLogs(),
  new \Jazzee\Console\CreateDemo(),
  new \Jazzee\Console\Scramble()
));
$cli->run();