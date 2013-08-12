<?php

namespace Jazzee\Console;

/**
 * Set a users API key
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class UserApiKey extends \Symfony\Component\Console\Command\Command
{

  /**
   * @see Console\Command\Command
   */
  protected function configure()
  {
    $this->setName('user-apikey')->setDescription('Set a users API Key.');
    $this->addArgument('username', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'User name to act on.  Use find-user to search for users in the directory.');
    $this->addArgument('apikey', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'The key to assign must be at least 32 characters.');
    $this->setHelp('Set a users API key manually.');
  }

  /**
   * @SuppressWarnings(PHPMD.ExitExpression)
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   */
  protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
  {
    $entityManager = $this->getHelper('em')->getEntityManager();
    $user = $entityManager->getRepository('\Jazzee\Entity\User')->findOneBy(array('uniqueName' => $input->getArgument('username')));
    if (!$user) {
      $output->write('<error>That user does not have an account on this system.  User add-user to create one.</error>' . PHP_EOL);
      exit();
    }
    $user->setApiKey($input->getArgument('apikey'));
    $entityManager->persist($user);
    $entityManager->flush();
    $output->write("<info>{$user->getLastName()}, {$user->getFirstName()} has key {$user->getApiKey()}</info>" . PHP_EOL);
  }

}