<?php

namespace Jazzee\Console;

/**
 * Sets up the first user
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class AddUser extends \Symfony\Component\Console\Command\Command
{

  /**
   * @see Console\Command\Command
   */
  protected function configure()
  {
    $this->setName('add-user')->setDescription('Add a new user.');
    $this->addArgument('user name', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Unser name for the new users.  Use find-user to search for users in the directory.');
    $this->setHelp('Add a new user.  This will allow the user to log into the system, but until you add them to at least one role they will not be able to do anything.');
  }

  protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
  {
    $jazzeeConfiguration = new \Jazzee\Configuration;
    $entityManager = $this->getHelper('em')->getEntityManager();
    $stub = new AdminStub;
    $stub = new AdminStub;
    $stub->em = $entityManager;
    $stub->config = $jazzeeConfiguration;
    $class = $jazzeeConfiguration->getAdminDirectoryClass();
    $directory = new $class($stub);

    $results = $directory->findByUniqueName($input->getArgument('user name'));
    if (count($results) == 0) {
      $output->write('<error>That user name is not in the directory.</error>' . PHP_EOL);
    } else if (count($results) > 1) {
      $output->write('<error>This name is not unique in the directory.  It returned ' . count($results) . ' results.</error>' . PHP_EOL);
      $output->write('<error>The name belongs to:</error>' . PHP_EOL);
      foreach ($results as $arr) {
        $output->write("<error>{$arr['lastName']}, {$arr['firstName']} ({$arr['emailAddress']})</error>" . PHP_EOL);
      }
    } else {
      $arr = $results[0];
      $user = new \Jazzee\Entity\User();
      $user->setUniqueName($arr['userName']);
      $user->setFirstName($arr['firstName']);
      $user->setLastName($arr['lastName']);
      $user->setEmail($arr['emailAddress']);
      $entityManager->persist($user);
      $entityManager->flush();
      $output->write("<info>{$arr['lastName']}, {$arr['firstName']} ({$arr['emailAddress']}) add as user #{$user->getId()}</info>" . PHP_EOL);
    }
  }

}