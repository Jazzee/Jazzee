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
class CreateAdminRole extends \Symfony\Component\Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this->setName('create-admin-role')->setDescription('Create an administrators group with enough permissions to setup other roles and the system.');
        $this->addArgument('name', \Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'The name of the role.  Defaults to Administrator');
        $this->setHelp('Run this command once when Jazzee is first installed to give your first users enough permissions to access the system and setup other roles.');
    }
    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output){
      $roleName = $input->getArgument('name')?$input->getArgument('name'):'Administrator';
      
      $em = $this->getHelper('em')->getEntityManager();
      $role = new \Jazzee\Entity\Role();
      $role->makeGlobal();
      $em->persist($role);
      $role->setName($roleName);
      $arr = array(
        'manage_users' => array('index', 'edit', 'new', 'reset'),
        'manage_roles' => array('index', 'edit', 'new')     
      );
      foreach($arr as $controller => $actions){
        foreach($actions as $action){
          $ra = new \Jazzee\Entity\RoleAction();
          $ra->setController($controller);
          $ra->setAction($action);
          $ra->setRole($role);
          $em->persist($ra);
        }
      }
      $em->flush();
      $output->write("<info>{$roleName} created.</info>" . PHP_EOL);
    }
}