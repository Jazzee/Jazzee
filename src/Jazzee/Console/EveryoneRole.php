<?php

namespace Jazzee\Console;

/**
 * Sets up the first user
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class EveryoneRole extends \Symfony\Component\Console\Command\Command
{

  /**
   * @see Console\Command\Command
   */
  protected function configure()
  {
    $this->setName('everyone-role')->setDescription('Put all users into a role.');
    $this->addArgument('role name', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'The name of the role.');
    $this->addOption('multiple', 'm', \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'If set users will be added to every role with that name.  Otherwise the name must resolve to exactly one role.');
    $this->addOption('global-only', 'g', \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'If set only global roles will be searched.');
    $this->setHelp('Put all system users into a role.');
  }

  /**
   * @SuppressWarnings(PHPMD.ExitExpression)
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   */
  protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
  {
    $entityManager = $this->getHelper('em')->getEntityManager();
    $users = $entityManager->getRepository('\Jazzee\Entity\User')->findAll();
    if (!$users) {
      $output->write('<error>There are no users in the system.  User add-user to create one.</error>' . PHP_EOL);
      exit();
    }
    $find = array('name' => $input->getArgument('role name'));
    if($input->getOption('global-only')){
      $find['isGlobal'] = true;
    }
    $roles = $entityManager->getRepository('\Jazzee\Entity\Role')->findBy($find);
    if (count($roles) == 0) {
      $output->write('<error>There are no roles with that name.</error>' . PHP_EOL);
      exit();
    }
    if (!$input->getOption('multiple') AND count($roles) > 1) {
      $output->write('<error>There are ' . count($roles) . ' roles with that name.  In order to add users to muliple roles you must use the --multiple option.</error>' . PHP_EOL);
      exit();
    }
    $results = array();
    foreach($entityManager->getRepository('\Jazzee\Entity\User')->findBy(array(), array('lastName' => 'ASC', 'firstName' => 'ASC')) as $user){
      foreach($roles as $role){
        if(!$user->hasRole($role)){
          $user->addRole($role);
          $results[] = "<info>{$user->getLastName()}, {$user->getFirstName()} added to {$role->getName()} role</info>";
        }
      }
      $entityManager->persist($user);
    }
    
    $entityManager->flush();
    $output->write(implode(PHP_EOL, $results) . PHP_EOL . '<info>Total of ' . count($results) . ' changes</info>' . PHP_EOL);
  }

}