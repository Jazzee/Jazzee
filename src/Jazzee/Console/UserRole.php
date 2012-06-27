<?php

namespace Jazzee\Console;

/**
 * Sets up the first user
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class UserRole extends \Symfony\Component\Console\Command\Command
{

  /**
   * @see Console\Command\Command
   */
  protected function configure()
  {
    $this->setName('user-role')->setDescription('Add a new user.');
    $this->addArgument('user name', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'User name to act on.  Use find-user to search for users in the directory.');
    $this->addArgument('role name', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'The name of the role.');
    $this->setHelp('Put a user in a role by name.');
  }

  /**
   * @SuppressWarnings(PHPMD.ExitExpression)
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   */
  protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
  {
    $entityManager = $this->getHelper('em')->getEntityManager();
    $user = $entityManager->getRepository('\Jazzee\Entity\User')->findOneBy(array('uniqueName' => $input->getArgument('user name')));
    if (!$user) {
      $output->write('<error>That user does not have an account on this system.  User add-user to create one.</error>' . PHP_EOL);
      exit();
    }

    $roles = $entityManager->getRepository('\Jazzee\Entity\Role')->findBy(array('name' => $input->getArgument('role name'), 'isGlobal' => true));
    if (count($roles) == 0) {
      $output->write('<error>There are no roles with that name.</error>' . PHP_EOL);
      exit();
    }
    if (count($roles) > 1) {
      $output->write('<error>There are ' . count($roles) . ' global roles with that name.  You will have to add the user to a different role or edit the names in the web interface.</error>' . PHP_EOL);
      exit();
    }
    $user->addRole($roles[0]);
    $entityManager->persist($user);
    $entityManager->flush();
    $output->write("<info>{$user->getLastName()}, {$user->getFirstName()} added to {$roles[0]->getName()} role</info>" . PHP_EOL);
  }

}