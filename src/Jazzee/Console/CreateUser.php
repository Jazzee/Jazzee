<?php

namespace Jazzee\Console;

/**
 * Create a new user
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class CreateUser extends \Symfony\Component\Console\Command\Command
{

  /**
   * @see Console\Command\Command
   */
  protected function configure()
  {
    $this->setName('create-user')->setDescription('Create a new user in the local directory.  If you have a real directory setup then use add-user');
    $this->addArgument('user name', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'User name for the new user.');
    $this->addArgument('email', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Email address for the new user');
    $this->addArgument('first name', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'First name for the new user');
    $this->addArgument('last name', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Last name for the new user');
    $this->setHelp('Create a new user.  This will allow the user to log into the system, but until you add them to at least one role they will not be able to do anything.');
  }

  protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
  {
    $jazzeeConfiguration = new \Jazzee\Configuration;
    if($jazzeeConfiguration->getAdminDirectoryClass() != '\Jazzee\AdminDirectory\Local'){
      $output->write('<error>You can only user create-user when the adminDirectoryClass is set to Jazzee\AdminDirectory\Local</error>' . PHP_EOL);
    }
    $entityManager = $this->getHelper('em')->getEntityManager();
    $existingUsers = $entityManager->getRepository('Jazzee\Entity\User')->findByUniqueName($input->getArgument('user name'));
    if(count($existingUsers) == 0){
      $user = new \Jazzee\Entity\User();
      $user->setUniqueName($input->getArgument('user name'));
      $user->setFirstName($input->getArgument('first name'));
      $user->setLastName($input->getArgument('last name'));
      $user->setEmail($input->getArgument('email'));
      $entityManager->persist($user);
      $entityManager->flush();
      $output->write("<info>New user #{$user->getId()} created</info>" . PHP_EOL);
    } else {
      $output->write("<error>There is already a user with the name {$input->getArgument('user name')}</error>" . PHP_EOL);
    }
  }

}