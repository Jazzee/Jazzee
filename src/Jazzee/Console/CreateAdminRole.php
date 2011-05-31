<?php
namespace Jazzee\Console;

/**
 * Sets up the first user
 *
 */
class CreateAdminRole extends \Symfony\Component\Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('create-admin-role')
        ->setDescription('Create an administrators group with enough permissions to setup other roles and the system.')
        ->setDefinition(array(
            new \Symfony\Component\Console\Input\InputOption(
                'name', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED,
                'The name of the role.'
            )
        ))
        ->setHelp('Run this command once when Jazzee is first installed to give your first users enough permissions to access the system and setup other roles.');
    }
    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output){
      if(!$input->getOption('name')){
        $output->write('<error>--name is required.</error>' . PHP_EOL);
        exit();
      }
      
      $em = $this->getHelper('em')->getEntityManager();
      $role = new \Jazzee\Entity\Role();
      $role->makeGlobal();
      $em->persist($role);
      $role->setName($input->getOption('name'));
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
    }
}