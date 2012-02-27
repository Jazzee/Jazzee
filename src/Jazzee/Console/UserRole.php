<?php
namespace Jazzee\Console;

/**
 * Sets up the first user
 *
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage console
 */
class UserRole extends \Symfony\Component\Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('user-role')
        ->setDescription('Add a new user.')
        ->setDefinition(array(
            new \Symfony\Component\Console\Input\InputOption(
                'uniqueName', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED,
                'Users identity name.'
            ),
            new \Symfony\Component\Console\Input\InputOption(
                'role', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED,
                'The name of the role.'
            )
        ))
        ->setHelp('Put a user in a role by name.');
    }
    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output){
      $error = false;
      if(!$input->getOption('uniqueName')){
        $output->write('<error>--uniqueName is required.</error>' . PHP_EOL);
        $error = true;
      }
      if(!$input->getOption('role')){
        $output->write('<error>--role is required.</error>' . PHP_EOL);
        $error = true;
      }
      if($error) exit(1);
      $em = $this->getHelper('em')->getEntityManager();
      $user = $em->getRepository('\Jazzee\Entity\User')->findOneBy(array('uniqueName'=>$input->getOption('uniqueName')));
      if(!$user){
        $output->write('<error>Bad user name.</error>' . PHP_EOL);
        exit();
      }
      
      $role = $em->getRepository('\Jazzee\Entity\Role')->findOneByName($input->getOption('role'));
      if(!$role){
        $output->write('<error>Bad role name.</error>' . PHP_EOL);
        exit();
      }
      $user->addRole($role);
      $em->persist($user);
      $em->flush();
    }
}